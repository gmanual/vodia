<?php
	$ini_array = parse_ini_file("config.ini");
        $username = $ini_array['api_username'];
        $password = $ini_array['api_password'];
	$auth_base64 = base64_encode($username.':'.$password);

	$header = array();
	$header[] = 'Authorization: Basic ' . $auth_base64;
	$header[] = 'Content-Type: application/json';

	$hosts = array('78.techpath.com.au');

	$blacklist_domains = array('localhost','outbound');
	
	foreach($hosts as $host){

		$url = 'https://' . $host . '/rest/system/domains';


		$ch = curl_init();
		$options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false
		);

		curl_setopt_array($ch , $options);
		$response = curl_exec($ch);
		curl_close($ch);

		$response_domain_decode = json_decode($response,true);

		foreach($response_domain_decode as $key=>$domain){
			$current_domain = $domain['name'];
			if ( !(in_array($current_domain,$blacklist_domains))){
				$techpath_domains[$host][] = $domain['name']; 
			}
		}
	}
	
	foreach($techpath_domains as $host=>$domains){
		foreach($domains as $domain){
			$url = 'https://' . $host . '/rest/domain/' . $domain . '/users/';
	
			$ch = curl_init();
			$options = array(
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false
			);
			curl_setopt_array($ch , $options);
			$response = curl_exec($ch);
			curl_close($ch);
			$response_users_decode = json_decode($response,true);

			foreach($response_users_decode as $users){
				if($users['usertype'] === 'acds'){
					$url = 'https://' . $host . '/rest/domain/' . $domain . '/user_settings/' . $users['name'];	
					$ch = curl_init();
					$options = array(
						CURLOPT_HTTPHEADER => $header,
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_SSL_VERIFYHOST => false,
						CURLOPT_SSL_VERIFYPEER => false
					);
					curl_setopt_array($ch , $options);
					$response = curl_exec($ch);
					curl_close($ch);
					$response_user_settings_decode = json_decode($response,true);
					if ($response_user_settings_decode['ext_blink'] != true){
						print_r($url); echo "\n";
					}					

				}
			}
			
		}
	}		
?>

