<?php

namespace Modules\Usermanagement\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Usermanagement\Models\User;

/**
 * Bonus fix alongside QAX Pentest Report 2026-08-19, finding 4.1
 * "Unauthenticated attachment download" (High Risk): e-signature images
 * had the exact same root cause (stored on the 'public' disk, reachable
 * through the storage:link symlink with no auth check ever running).
 *
 * Moves every users.sign file still sitting under the public disk over to
 * the 'private' disk. The `sign` column itself doesn't need to change
 * (it only ever stored the filename, not the disk), so there is no DB
 * write here — just a filesystem move plus a sanity check.
 *
 * Usage:
 *   php artisan usermanagement:migrate-signatures-to-private             # dry run
 *   php artisan usermanagement:migrate-signatures-to-private --apply      # do it for real
 *   php artisan usermanagement:migrate-signatures-to-private --apply --keep-old
 */
class MigrateSignaturesToPrivateDiskCommand extends Command
{
    protected $signature = 'usermanagement:migrate-signatures-to-private
                            {--apply : Actually perform the migration. Without this flag, only a dry-run report is printed.}
                            {--keep-old : Do not delete the old file from the public disk after a successful copy.}';

    protected $description = 'Move user e-signature images from the public disk to the private disk (fix for QAX pentest finding 4.1 sibling bug).';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $keepOld = (bool) $this->option('keep-old');

        if (!$apply) {
            $this->warn('Dry-run mode. No files will be changed. Pass --apply to execute.');
        }

        $public = Storage::disk('public');
        $private = Storage::disk('private');

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        User::withTrashed()
            ->whereNotNull('sign')
            ->where('sign', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($public, $private, $apply, $keepOld, &$migrated, &$skipped, &$failed) {
                foreach ($users as $user) {
                    $path = 'signatures/' . $user->id . '/' . $user->sign;
                    $label = "User #{$user->id} signature ({$path})";

                    if ($private->exists($path)) {
                        $this->line("<fg=green>OK</>   {$label} — already on private disk.");
                        $skipped++;

                        continue;
                    }

                    if (!$public->exists($path)) {
                        $this->line("<fg=red>FAIL</> {$label} — not found on either disk.");
                        $failed++;

                        continue;
                    }

                    if (!$apply) {
                        $this->line("<fg=cyan>WOULD MOVE</> {$label}");
                        $migrated++;

                        continue;
                    }

                    try {
                        $stream = $public->readStream($path);

                        if ($stream === false || $stream === null) {
                            throw new \RuntimeException('Could not open read stream on source disk.');
                        }

                        $private->put($path, $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }

                        if (!$private->exists($path)) {
                            throw new \RuntimeException('Copy verification failed.');
                        }

                        if (!$keepOld) {
                            $public->delete($path);
                        }

                        $this->line("<fg=green>OK</>   {$label} — moved to private disk.");
                        $migrated++;
                    } catch (\Throwable $e) {
                        $this->line("<fg=red>FAIL</> {$label} — {$e->getMessage()}");
                        $failed++;
                    }
                }
            });

        $this->newLine();
        $this->info("Signatures: {$migrated} migrated, {$skipped} skipped (already private), {$failed} failed.");

        if (!$apply) {
            $this->comment('Re-run with --apply once you are happy with the numbers above.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}