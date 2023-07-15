<?php
	session_start();

	require 'PHPMailer/PHPMailer.php';
	require 'PHPMailer/SMTP.php';
	require 'PHPMailer/Exception.php';
	require_once 'includes/config.php';

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	$mail = new PHPMailer(true);

	$statusTitle = '';
	$statusMsg = '';
	$statusInfo = '';

	if(isset($_POST['submit'])){
		// Check if the last submission time is stored in the session
		if (isset($_SESSION['last_submission_time'])) {
			// Calculate the time elapsed since the last submission
			$currentTime = time();
			$lastSubmissionTime = $_SESSION['last_submission_time'];
			$timeElapsed = $currentTime - $lastSubmissionTime;
		}

		// Check if less than 30 minutes have passed since the last submission
		if (isset($_SESSION['last_submission_time']) && $timeElapsed < 1800) { // 30 minutes = 30 * 60 seconds = 1800 seconds
			$statusTitle = 'Oops!';
			$statusMsg = 'You can only submit the form once every 30 minutes.';
			$statusInfo = 'Please try again later.';
		} else {
			if(isset($_POST['captcha-response']) && !empty($_POST['captcha-response'])){
				// Get verify response data
				$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$_POST['captcha-response']);
				$responseData = json_decode($verifyResponse);
				if($responseData->success){
					
					if (isset($_POST['email'])) {
	
						$name = $_POST['name']; // required
						$email_from = $_POST['email']; // required
						$price = $_POST['price']; // not required
						$comments = $_POST['comments']; // required
				
							try {
								// Enable verbose debugging (optional)
								// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
							
								// Set mailer to use SMTP
								$mail->isSMTP();
							
								// Specify SMTP server settings
								$mail->Host = $mail_host;
								$mail->SMTPAuth = true;
								$mail->Username = $mail_username;
								$mail->Password = $mail_password;
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
								$mail->Port = 587;
							
								// Set sender and recipient
								$mail->setFrom($mail_from_email, $mail_from_name);
								$mail->addAddress($mail_to_email, $mail_to_name);
				
								$mail->isHTML(true); // Set email format to HTML
							
								// Set email subject and body
								$mail->Subject = 'Domain Inquiry - ' . $_SERVER['SERVER_NAME'];
								$mail->Body = 'Inquiry details below.<br><br>' .
									'Name: ' . $name . '<br>' .
									'Email: ' . $email_from . '<br>' .
									'Offer: $' . $price . '<br>' .
									'Message: ' . $comments . '<br>';
							
								// Send the email
								$mail->send();
								// Update the last submission time in the session
								$_SESSION['last_submission_time'] = time();
							
								// Email sent successfully
							} catch (Exception $e) {
								// Error occurred while sending email
								// echo 'Email could not be sent. Error: ' . $mail->ErrorInfo;
								
								header('Location: /');
							}
						}
		
					$statusTitle = 'Thank you!';
					$statusMsg = 'Your offer has been sent.';
					$statusInfo = 'You should receive a reply from the owner within the next 24 hours.';
				}else{
					$statusTitle = 'Oops!';
					$statusMsg = 'Robot verification failed, please try again.';
					$statusInfo = 'If the problem persists, please contact the owner directly.';
				}
			}else{
				$statusTitle = 'Oops!';
				$statusMsg = 'Robot verification failed, please try again.';
				$statusInfo = 'If the problem persists, please contact the owner directly.';
			}
		}
	}	
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Submission - <?php echo $_SERVER['SERVER_NAME'] ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Mukta+Mahee:300,700,800" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <link rel="stylesheet" href="/css/styles.css" />
    </head>
    <body>
        <div class="wrapper">
            <div>
                <div>
                    <span>
                        <strong><?php echo $statusTitle; ?></strong>
                        <span><?php echo $statusMsg; ?></span>
                    </span>
                    <p><?php echo $statusInfo; ?></p>
                    <div class="mailto">
                        <i class="fa-regular fa-comment"></i>
                        <a href="mailto:<?php echo $your_email ?>"><?php echo $your_email ?></a>
                    </div>
                </div>
            </div>
            <div>
                <div class="whoami">
                    <strong><?php echo $your_name ?></strong>
					<span><?php echo $your_location ?></span>
					<span><?php echo $your_bio ?></span>
					<a href="<?php echo $your_website ?>" target="_blank"><?php echo $your_email ?></a>
				</div>
            </div>
        </div>
		</body>
</html>