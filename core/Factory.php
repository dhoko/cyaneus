<?php 
class Factory extends Cyaneus {

	/**
	 * Create a static files in DRAFT from webHook files.
	 * @param  Array $files Array of files from WebHook
	 */
	public static function build(Array $data) {
		foreach ($data as $files) {
			if(!file_exists(DRAFT.DIRECTORY_SEPARATOR.$files['folder']))
					mkdir(DRAFT.DIRECTORY_SEPARATOR.$files['folder']);
				
			if(file_exists(DRAFT.DIRECTORY_SEPARATOR.$files['path'])) unlink(DRAFT.DIRECTORY_SEPARATOR.$files['path']);
			file_put_contents(DRAFT.DIRECTORY_SEPARATOR.$files['path'],$files['content'] );
			klog('Build file success for '.$files['path']);
		}
	}

	/**
	 * Delete a file if we delete it from a commit
	 * @param  Array $files Array of files from WebHook
	 */
	public static function destroy(Array $files) {
		foreach ($files as $e) {
			if(file_exists(DRAFT.DIRECTORY_SEPARATOR.$e['path'])) unlink(DRAFT.DIRECTORY_SEPARATOR.$e['path']);
			
			klog('Delete file success for '.$e['path']);
		}
	}

}