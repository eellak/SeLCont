<?php


/**
 * Function name
 *
 * what the function does
 *
 * @param string $dir path where the screenshots are stored
 * @param string $out return xml for: 
 * seconds from start if 's', timestamps if 't', both if 'b'
 * @param string $outpath path where the xml(s) will be stored
 * 
 */
function readTimestamps($dir,$out,$outpath){

	if (is_dir($dir)) {
	    if ($dh = opendir($dir)) {	    	
			$timestamps = array();
			$seconds = array();
	        while (($file = readdir($dh)) !== false) {

	            $t_tmp= explode(".",$file);
				$t_arr = explode("_",$t_tmp[0]);
				$t_str = ($t_arr[2]."-".$t_arr[1]."-".$t_arr[0]." ".$t_arr[3].":".$t_arr[4].":".$t_arr[5]);
				
				$unix_t = strtotime($t_str);
				if (! empty($unix_t)) {				
					if (empty($seconds)){
						array_push($seconds, '0');						
					}else{
						$time_diff = $unix_t - end($timestamps);
						array_push($seconds, $time_diff);
					}
					array_push($timestamps, $unix_t);
				}	
	        }
	        closedir($dh);
	    }
	}
	
	$xml_t = new SimpleXMLElement('<timestamps/>');
	array_flip($timestamps);
	array_walk_recursive(array_flip($timestamps), array ($xml_t, 'addChild'));
	print $xml_t->asXML();	
	
	$xml_s = new SimpleXMLElement('<seconds/>');
	array_flip($seconds);
	array_walk_recursive(array_flip($seconds), array ($xml_s, 'addChild'));
	print $xml_s->asXML();
	
	if ($out=="t" || $out=="b" ){
		file_put_contents($outpath.'/timestamps.xml', $xml_t->asXML());
	}
	if ($out=="s" || $out=="b" ){
		file_put_contents($outpath.'/seconds.xml', $xml_s->asXML()); 		
	}	
}



var_dump($argv);
readTimestamps('$argv[1]','$argv[2]','$argv[3]')

?>