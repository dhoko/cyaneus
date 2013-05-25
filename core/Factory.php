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

	/**
	 * Will find each drafts from DRAFT. 
	 * File must have these extensions : md|markdown
	 * @return Array array of ['build':timestamp,file,path]
	 */
	public static function find() {
		$files          = array(); 
		$readable_draft = array('md','markdown');
		$draftPath      = dirname(__FILE__).DIRECTORY_SEPARATOR.DRAFT.DIRECTORY_SEPARATOR;
		$iterator       = new RecursiveDirectoryIterator($draftPath,RecursiveIteratorIterator::CHILD_FIRST);

		klog('Looking for drafts');
		foreach(new RecursiveIteratorIterator($iterator) as $file) {
			if($file->isFile()) {
				$md5 = md5($file->getPath());
				if (in_array($file->getExtension(), $readable_draft)) {
					$files[$md5]['draft'] = array(
						'build' => $file->getMTime(),
						'file'  => $file->getfilename(),
						'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
					);
				}
				if( in_array($file->getExtension(), array("jpg",'png','gif','jpeg')) ) {
					$files[$md5]['pict'] = array(
						'build' => $file->getMTime(),
						'file'  => $file->getfilename(),
						'path'  => $file->getPath().DIRECTORY_SEPARATOR.$file->getfilename()
					);
				}

				if(empty($files[$md5]['draft'])) unset($files[$md5]);
			} 
		}
		return $files;
	}

}