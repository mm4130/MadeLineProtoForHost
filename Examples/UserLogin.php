#!/usr/bin/env php
<?php
	//print_r($_SERVER);
	require_once 'config.php'; // فایل کانفیگ
	require_once $libPath.'vendor/autoload.php'; // فراخوانی لودر کتابخانه میدلاین
	//require_once $libPath.'src/danog/MadelineProto/VoIP/php-libtgvoip.php';
	
	if(file_exists('inc/WeCanFunctions.php')){
		require_once('inc/WeCanFunctions.php'); // توابع کاربردی
	}
	if(file_exists('inc/SocksProxy.php')){
		require_once('inc/SocksProxy.php'); // پروکسی
	}
	
	if(!file_exists($sessionsDir)){
		mkdir($sessionsDir);
	}
	
	file_put_contents('LastRun',date("Y-m-d H:i:s", time()));
	
	exec("ps aux", $psRes);
	$psResS = implode("\n",$psRes);
	$UserBotF = getcwd().'/UserBot.php';
	$UserBotF = explode("/",$UserBotF);
	unset($UserBotF[0]);
	unset($UserBotF[1]);
	$UserBotF = implode("/",$UserBotF);
	$ProcessCount=0;
	foreach($psRes as $processLine){
		if((strpos($processLine, $UserBotF) !== false)){
			$ProcessCount++;
		}
	}
	
	if($ProcessCount > 2){
		echo "stop: ";
		print_r($psRes);
		//file_put_contents('LastRun2',date("Y-m-d H:i:s", time())."\n--------\n".implode("\n",$psRes));
		exit();
	}

	
	$BreakLine = "<br>";
	if( (isset($_SERVER['SESSIONNAME']) && strpos(strtolower($_SERVER['SESSIONNAME']), 'console') !== false) || isset($_SERVER['SHELL']) ){
		$RunInTerminal = true;
	}
	
	if($RunInTerminal){
		if(isset($argv[1])){
			if(trim($argv[1]) !=""){
				$_GET['phone'] = $argv[1];
			}
			if(isset($argv[2])){
				if(trim($argv[2]) !=""){
					$_GET['code'] = $argv[2];
				}
			}
			if(isset($argv[3])){
				if(trim($argv[3]) !=""){
					$_GET['pass'] = $argv[3];
				}
			}
		}else{
			$_GET['phone'] = readline('Shomare Hamrahe Khod Ra Vared Namaed: (Phone Number) ');
		}
		$BreakLine = "\n";
	}else{
		echo '
		<html dir="rtl">
			<style>
				input[type=text]{
					width: 250px;
				}
			</style>
			<body style="direction: rtl;font-family:tahoma;font-size: 12px;">
		';
	}
	
	global $phones;
	$phones=array();
	if(isset($_GET['phone'])){
		$phones[0]['number'] = $_GET['phone'];
		$phones[0]['active'] = true;
		$phones[0]['current'] = true;
	}else{
		echo '
			<form action="" method="">
				<input type="text" name="phone" style="direction:ltr;"  placeholder="شماره همراه خود را وارد نمایید..." />
				<input type="submit" value="اتصال">
			</form>
		
		';
		exit();
	}
	
	//$MySettings = $settings_proxy;

	$phones[0]['number'] = str_replace(array(" ","(",")"),"",$phones[0]['number']); // شماره موبایلی که با آن لاگین میشوید
	$sessionFile = $sessionsDir."/.session_".str_replace(array("+","-","(",")"),"",$phones[0]['number']).""; // مسیر سشن

	$MadelineProto[$phones[0]['number']] = false;
	echo "درحال آماده سازی...". PHP_EOL .$BreakLine;

	if(file_exists($sessionFile)){
		try {
			echo 'درحال خواندن سشن: ('.$sessionFile.')...'. PHP_EOL .$BreakLine;
			//RemoveProxies($sessionFile);
			$MadelineProto[$phones[0]['number']] = \danog\MadelineProto\Serialization::deserialize($sessionFile);
			echo 'سشن خوانده شد.'. PHP_EOL .$BreakLine;
			if(!$RunInTerminal){
				echo '<a href="./UserBot.php">توقف اکانت</a>'. PHP_EOL .$BreakLine;
			}
			// set proxy
			//$MadelineProto[$phones[0]['number']]->settings['logger']['logger'] = $MySettings['logger']['logger'];
			//$MadelineProto[$phones[0]['number']]->settings['connection_settings'] = $MySettings['connection_settings'];
			//$MadelineProto[$phones[0]['number']]->settings['app_info'] = $MySettings['app_info'];
			
			// remove proxy
			//unset($MadelineProto[$phones[0]['number']]->settings['connection_settings']['all']['proxy']);
			//unset($MadelineProto[$phones[0]['number']]->settings['connection_settings']['all']['proxy_extra']);
			//$MadelineProto[$phones[0]['number']]->updates->API->chats = null;
			//$MadelineProto[$phones[0]['number']]->updates->API->full_chats = null;
			//$MadelineProto[$phones[0]['number']]->updates->API->updates = null;
			//$MadelineProto[$phones[0]['number']]->updates->API->constructors = null;
			//$MadelineProto[$phones[0]['number']]->updates->API->methods = null;
			//for($i=0; $i<sizeof($MadelineProto[$phones[0]['number']]->updates->API->datacenter->sockets); $i++){
				//$MadelineProto[$phones[0]['number']]->updates->API->datacenter->sockets[$i]->extra = [];
				//$MadelineProto[$phones[0]['number']]->updates->API->datacenter->sockets[$i]->proxy = '\Socket';
			//}
			//foreach ($MadelineProto[$phones[0]['number']]->updates->API->datacenter->sockets as $key => $socket) {
				//print_r($MadelineProto[$phones[0]['number']]->updates->API->datacenter->sockets[$key]);
				//exit();
			//}
			//$MadelineProto[$phones[0]['number']]->updates->API->phone->API = null;
			//$MadelineProto[$phones[0]['number']]->updates->API->stickers->API = null;
			
			
		} catch (\danog\MadelineProto\Exception $e) {
			echo 'خطا: '. PHP_EOL .$BreakLine;
			var_dump($e->getMessage());
			exit();
		}
	}

	if ($MadelineProto[$phones[0]['number']] === false) {
		sleep(0.5);
		echo 'درحال اتصال به سرور تلگرام...'.PHP_EOL;
		$MadelineProto[$phones[0]['number']] = new \danog\MadelineProto\API($settings);
		echo 'به سرور تلگرام متصل شد.'. PHP_EOL .$BreakLine;

		echo 'درحال چک کردن شماره موبایل...'. PHP_EOL .$BreakLine;
		$checkedPhone = $MadelineProto[$phones[0]['number']]->auth->checkPhone(['phone_number' => $phones[0]['number'],]);
		echo 'موبایل چک شد.'. PHP_EOL .$BreakLine;

		echo 'درحال ارسال کد جهت ورود به اکانت...'. PHP_EOL .$BreakLine;
		$sentCode = $MadelineProto[$phones[0]['number']]->phone_login($phones[0]['number']);
		$phones_code_hash = $sentCode['phone_code_hash'];
		\danog\MadelineProto\Serialization::serialize($sessionFile, $MadelineProto[$phones[0]['number']]);
		if($phones_code_hash !==""){
			if($RunInTerminal){
				$_GET['code'] = readline('Code Taeed Ra Vared Namaed: ');
			}else{
				echo 'کد به تلگرام شما ارسال شد.'. PHP_EOL .$BreakLine;
				echo '
				<form action="" method="">
					<input type="hidden" name="phone" value="'.$_GET['phone'].'" />
					<input type="text" name="code" style="direction:ltr;"  placeholder="کد تایید را وارد نمایید..." />
					<input type="submit" value="تایید کد">
				</form>
				';
				exit();
			}
		}else{
			echo 'خطا در ارسال کد.'. PHP_EOL .$BreakLine;
			exit();
		}
	}
	
	if($MadelineProto[$phones[0]['number']] != false && isset($_GET['code'])){
		$code = $_GET['code'];
		echo 'درحال تایید کد...'. PHP_EOL .$BreakLine;
		$authorization = $MadelineProto[$phones[0]['number']]->complete_phone_login($code);

		if ($authorization['_'] === 'account.noPassword') {
			echo 'ورود دو مرحله ای شما فعال است و پسورد خود را وارد نکردید!'. PHP_EOL .$BreakLine;
			exit();
		}
		if ($authorization['_'] === 'account.password') {
			$help = $authorization['hint']; // راهنمای پسورد
			if($RunInTerminal){
				$_GET['pass'] = readline('Password: ('.$help.')');
			}else{
				if(!isset($_GET['pass'])){
					echo "راهنمای پسورد اکانت: ".$help. PHP_EOL .$BreakLine;
					echo '
					<form action="" method="">
						<input type="hidden" name="phone" value="'.$_GET['phone'].'" />
						<input type="hidden" name="code" value="'.$_GET['code'].'" />
						<input type="password" name="pass" placeholder="رمز اکانت خود را وارد نمایید..." />
						<input type="submit" value="تایید رمز">
					</form>
					';
					exit();
				}
			}
			// ورود دو مرحله ای
			$authorization = $MadelineProto[$phones[0]['number']]->complete_2fa_login($_GET['pass']);
		}
		if ($authorization['_'] === 'account.needSignup') {
			// اگر برای اولین بار است که اکانت تلگرام روی این شماره فعال می شود، نام و نام خانوادگی را دریافت کن
			if($RunInTerminal){
				$_GET['first_name'] = readline('Name Shoma: ');
				$_GET['last_name'] = readline('Name Khanevadegi Shoma: ');
			}else{
				if(!isset($_GET['first_name'])){
					echo "درحال ثبت نام اکانت...". PHP_EOL .$BreakLine;
					echo '
					<form action="" method="">
						<input type="hidden" name="phone" value="'.$_GET['phone'].'" />
						<input type="hidden" name="code" value="'.$_GET['code'].'" />
						<input type="hidden" name="pass" value="'.$_GET['pass'].'" />
						<input type="text" name="first_name" placeholder="نام کوچک شما..." />
						<input type="text" name="last_name" placeholder="نام خانوادگی شما..." />
						<input type="submit" value="ثبت نام">
					</form>
					';
					exit();
				}
			}
			$authorization = $MadelineProto[$phones[0]['number']]->complete_signup($_GET['first_name'],$_GET['last_name']);
		}
		echo 'کد تایید شد.'. PHP_EOL .$BreakLine;
		$updates = $MadelineProto[$phones[0]['number']]->get_updates();
		$MadelineProto[$phones[0]['number']]->API->get_updates_difference();
		$MadelineProto[$phones[0]['number']]->API->store_db([], true);
		$MadelineProto[$phones[0]['number']]->API->reset_session();
		\danog\MadelineProto\Serialization::serialize($sessionFile, $MadelineProto[$phones[0]['number']]);
		echo 'حالا میتوانید با از سشن زیر جهت استفاده از اکانت خود استفاده نمایید:'. PHP_EOL .$BreakLine;
		echo $sessionFile. PHP_EOL .$BreakLine;
	}
