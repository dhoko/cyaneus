<?php
/**
 * Get content of a repository from a Github Webhook
 */
class GithubListener extends AbstractHookListener
{
    /**
     * Execute a WGET command to download the zip from
     * REPOSITORY_URL. Then it will extract it
     */
    public function get() {

        // Remove old one
        if(file_exists(DRAFT)) {
            exec(escapeshellcmd('rm -r '.DRAFT).' 2>&1', $rmr_output, $rmr_error);
        }

        $wget = '/usr/bin/wget --no-check-certificate  ';
        $url = REPOSITORY_URL;
        $file = __DIR__.DIRECTORY_SEPARATOR.'file.zip';

        exec(escapeshellcmd($wget.$url.' -O '.$file).' 2>&1', $wget_output, $wget_error);

        if($wget_error) {
            throw new Exception('An error has occurred with wget: '.var_export($wget_output, true));
        }

        $this->extract($file);
    }
}
