<?php
/**
 * Get content of a repository from a Github Webhook
 */
class GithubListener extends AbstractHookListener
{
    /**
     * Execute a WGET command to download the zip from
     * REPOSITORY_URL. Then it will extract it
     * @throws RuntimeException If Something goes wrong during the wget process
     */
    public function get()
    {
        // Remove old one
        if(file_exists(Cyaneus::config('path')->draft)) {
            exec(escapeshellcmd('rm -r '.Cyaneus::config('path')->draft).' 2>&1', $rmr_output, $rmr_error);
        }

        $wget = '/usr/bin/wget --no-check-certificate  ';
        $url  = Cyaneus::config('path')->repositoryUrl;
        $file = __DIR__.DIRECTORY_SEPARATOR.'file.zip';

        exec(escapeshellcmd($wget.$url.' -O '.$file).' 2>&1', $wget_output, $wget_error);

        if($wget_error) {
            throw new RuntimeException('An error has occurred with wget: '.var_export($wget_output, true));
        }

        $this->extract($file);
    }
}
