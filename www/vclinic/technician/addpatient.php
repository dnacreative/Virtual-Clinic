<?php 
	require_once('../../../include/vclinic/techniciansession.php');
	require_once('../'.VC_INCLUDE.'library.php');

	$showerror = false;
	$error = "";

	$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) or die('Error connecting to database');
	$query = "SELECT state_id, name FROM vc_address_state ORDER BY name";
	$data = mysqli_query($dbc, $query);

	if(!$data) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}

	$states = array();
	while($result = mysqli_fetch_array($data)) 
		array_push($states, $result);

	if(isset($_POST['submit'])) {
		$fname = mysqli_real_escape_string($dbc, trim($_POST['fname']));
		$lname = mysqli_real_escape_string($dbc, trim($_POST['lname']));
		if(isset($_POST['gender']))
			$gender = mysqli_real_escape_string($dbc, trim($_POST['gender']));
		else 
			$gender = NULL;
		$birthdate = mysqli_real_escape_string($dbc, trim($_POST['birthdate']));
		$occupation = mysqli_real_escape_string($dbc, trim($_POST['occupation']));
		$line1 = mysqli_real_escape_string($dbc, trim($_POST['line1']));
		$line2 = mysqli_real_escape_string($dbc, trim($_POST['line2']));
		$city = mysqli_real_escape_string($dbc, trim($_POST['city']));
		$district = mysqli_real_escape_string($dbc, trim($_POST['district']));
		$state = mysqli_real_escape_string($dbc, trim($_POST['state']));
		$pincode = mysqli_real_escape_string($dbc, trim($_POST['pincode']));
		$email = mysqli_real_escape_string($dbc, trim($_POST['email']));
		$phone = mysqli_real_escape_string($dbc, trim($_POST['phone']));

		if(isset($_POST['upload_type']))
			$upload_type = mysqli_real_escape_string($dbc, trim($_POST['upload_type']));
		else
			$upload_type = VC_UPLOAD_NONE;

		$picture_src_location = "";

		if($upload_type == VC_UPLOAD_FILE) {
			$picture_name = mysqli_real_escape_string($dbc, trim($_FILES['picture']['name']));
			$picture_type = mysqli_real_escape_string($dbc, trim($_FILES['picture']['type']));
			$picture_size = mysqli_real_escape_string($dbc, trim($_FILES['picture']['size']));
			$picture_src_location = mysqli_real_escape_string($dbc, trim($_FILES['picture']['tmp_name']));
		}
		else if($upload_type == VC_UPLOAD_PHOTO) {
			$photo = mysqli_real_escape_string($dbc, trim($_POST['encoded_picture']));
		}

		if(!empty($fname) && !empty($lname)) {
			if(!preg_match(VC_PATTERN_NAME, $fname)) {
				$showerror = true;
				$error = "First name must contain between 2 and 40 letters only.";
			}
			if(!preg_match(VC_PATTERN_NAME, $lname)) {
				$showerror = true;
				$error = "Last name must contain between 2 and 40 letters only.";
			}
			if(!preg_match(VC_PATTERN_OCCUPATION, $occupation)) {
				$showerror = true;
				$error = "Occupation field exceeds 40 characters.";
			}
			if(!preg_match(VC_PATTERN_CITY, $city)) {
				$showerror = true;
				$error = "Entered city must contain a maximum of 40 letters only.";
			}
			if(!preg_match(VC_PATTERN_CITY, $district)) {
				$showerror = true;
				$error = "Entered district must contain a maximum of 40 letters only.";
			}
			if(!empty($pincode)) {
				if(!preg_match(VC_PATTERN_PINCODE, $pincode)) {
					$showerror = true;
					$error = "Pincode must consist of six numbers exactly.";
				}
			}
			if(!preg_match(VC_PATTERN_ADDRESS, $line1)) {
				$showerror = true;
				$error = "Line 1 of address exceed 80 characters.";
			}
			if(!preg_match(VC_PATTERN_ADDRESS, $line2)) {
				$showerror = true;
				$error = "Line 2 of address exceeds 80 characters.";
			}
			if(!check_email($email)) {
				$showerror = true;
				$error = "You have not entered a valid email address.";
			}
			if(!preg_match(VC_PATTERN_PHONE, $phone)) {
				$showerror = true;
				$error = "You have not entered a valid phone number.";
			}

			if($showerror)
				remove_file($upload_type, $picture_src_location);

			if(!$showerror) {
				if($upload_type == VC_UPLOAD_FILE) {
					if(!empty($picture_name)) {
						if(($picture_type == 'image/png') || ($picture_type == 'image/pjpeg') || ($picture_type == 'image/jpeg')) {
							if(($picture_size > 0) && ($picture_size <= VC_MAXFILESIZE)) {
								if($_FILES['picture']['error'] == 0) {
									list($width, $height) = getimagesize($picture_src_location);

									if ($width < $height) 
										$side = $width;
									else
										$side = $height;
									$image_resampled = imagecreatetruecolor(VC_DPWIDTH, VC_DPHEIGHT);
									if(($picture_type == 'image/jpeg') || ($picture_type == 'image/pjpeg')) 
										$image_original = imagecreatefromjpeg($picture_src_location);
									else
										$image_original = imagecreatefrompng($picture_src_location);

									imagecopyresampled($image_resampled, $image_original, 0, 0, 0, 0, VC_DPWIDTH, VC_DPHEIGHT, $side, $side);
									$picture = "";
								}
								else {
									unlink($picture_src_location);
									$showerror = true;
									$error = "An error related to the uploaded picture occured";
								}
							}
							else {
								unlink($picture_src_location);
								$showerror = true;
								$error = "Picture size cannot exceed ".(VC_MAXFILESIZE/(1024*1024))."MB.";
							}
						}
						else {
							unlink($picture_src_location);
							$showerror = true;
							$error = "Picture format can only be JPG or PNG.";
						} 
					}
					else {
						unlink($picture_src_location);
						$showerror = true;
						$error = "You have not specified a file for your display picture.";
					}
				}
				else if($upload_type == VC_UPLOAD_PHOTO){
					$decoded_photo = base64_decode($photo);
				}
				else {
					$picture = 'default.png';
				}				
			}
			if(!$showerror) {
				if(empty($line1) && empty($line2) && empty($city) && empty($district) && empty($state) && empty($pincode))
					$query_address .= "NULL, ";
				else {
					$query2 = "INSERT INTO vc_address (line1, line2, city, district, state_id, pincode) VALUES ("; 

					if(empty($line1))
						$query2 .= "NULL, ";
					else
						$query2 .= "'$line1', ";

					if(empty($line2))
						$query2 .= "NULL, ";
					else
						$query2 .= "'$line2', ";

					if(empty($city))
						$query2 .= "NULL, ";
					else
						$query2 .= "'$city', ";

					if(empty($district))
						$query2 .= "NULL, ";
					else
						$query2 .= "'$district', ";

					if($state == '0')
						$query2 .= "NULL, ";
					else {
						$query2 .= "'$state', ";
					}

					if(empty($pincode))
						$query2 .= "NULL)";
					else
						$query2 .= "'$pincode')";

					if(mysqli_query($dbc, $query2)) {
						$query3 = "SELECT LAST_INSERT_ID()";
						$data = mysqli_query($dbc, $query3);
						if(mysqli_num_rows($data) != 1) {
							echo '<p class="error">Some error occured.</p>';
							exit();
						}
						$result = mysqli_fetch_array($data);

						$query_address .= $result['LAST_INSERT_ID()'].", ";
					}
					else {
						echo '<p class="error">Some error occured.</p>';
						exit();
					}
				}
			
				$query1 = "INSERT INTO vc_patient (fname, lname, gender, birthdate, occupation, address_id, email, phone, picture) VALUES ('$fname', '$lname', ";

				if(empty($gender))
					$query1 .= "NULL, ";
				else 
					$query1 .= "'$gender', ";

				if(empty($birthdate))
					$query1 .= "NULL, ";
				else
					$query1 .= "'$birthdate', ";

				if(empty($occupation))
					$query1 .= "NULL, ";
				else
					$query1 .= "'$occupation', ";

				$query1 .= $query_address;				

				if(empty($email))
					$query1 .= "NULL, ";
				else
					$query1 .= "'$email', ";

				if(empty($phone))
					$query1 .= "NULL, ";
				else
					$query1 .= "'$phone', ";

				$query1 .= "'default.png')";

				if(mysqli_query($dbc, $query1)) {
					$query3 = "SELECT LAST_INSERT_ID()";
					$data = mysqli_query($dbc, $query3);
					if(mysqli_num_rows($data) != 1) {
						echo '<p class="error">Some error occured.</p>';
						exit();
					}
					$result = mysqli_fetch_array($data);

					if($picture != 'default.png') {
						$picture = $result['LAST_INSERT_ID()'].'-'.time().'-p.png';;
						$picture_dest_location = '../'.VC_UPLOADPATH.$picture;

						$fp = fopen($picture_dest_location, 'w');
						fwrite($fp, $decoded_photo);
						fclose($fp);

						$query3 = "UPDATE vc_patient SET picture='$picture' WHERE patient_id=".$result['LAST_INSERT_ID()'];
						if(!mysqli_query($dbc, $query3)) {
							echo '<p class="error">Some error occured.</p>';
							exit();
						}

						unlink($picture_src_location);
					}
					mysqli_close($dbc);
					$url = VC_LOCATION.'patient.php?patient_id='.$result['LAST_INSERT_ID()'];
					header('Location: '.$url);
					exit();
				}
				else {
					remove_file($upload_type, $picture_src_location);
					$showerror = true;
					$error = "Birth date must be formatted as YYYY-MM-DD. Set it to blank if you do not want to enter a birth date.";
				}
			}
		}
		else {
			remove_file($upload_type, $picture_src_location);
			$showerror = true;
			$error = "First name and last name fields cannot be blank.";
		}
	}
?>

<?php require_once('../'.VC_INCLUDE.'startdocument.php'); ?>

	<link rel="stylesheet" href="<?php echo VC_LOCATION.'stylesheets/user.css'; ?>">
	<link rel="stylesheet" href="<?php echo VC_LOCATION.'stylesheets/adduser.css'; ?>">
	<link rel="stylesheet" href="<?php echo VC_LOCATION.'stylesheets/addpatient.css'; ?>">
	<link rel="stylesheet" href="<?php echo VC_LOCATION.'stylesheets/takepic.css'; ?>">
	<script src="<?php echo VC_LOCATION.'scripts/takepic.js'; ?>"></script>
</head>
<body>
	<div id="banner">
		<h2><?php echo "Add Patient"; ?></h2>
	</div>

	<div id="main-content">
		<?php if($showerror) echo '<p class="error">'.$error.'</p>'."\n"; ?>
		<div id="content">
			<form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
				<input type="hidden" id="encoded_picture" name="encoded_picture" value="">
				<table>
					<tr>
						<th><label for="fname">First Name: </label></th>
						<td><input type="text" id="fname" name="fname" value="<?php if(!empty($fname)) echo $fname; ?>"></td>
					</tr>
					<tr>
						<th><label for="lname">Last Name: </label></th>
						<td><input type="text" id="lname" name="lname" value="<?php if(!empty($lname)) echo $lname; ?>"></td>
					</tr>
					<tr>
						<th>Gender: </th>
						<td>
							<input type="radio" id="gender_m" name="gender" value="m" <?php if(!empty($gender) && $gender == 'm') echo 'checked="checked"'; ?>><label for="gender_m">Male </label>
							<input type="radio" id="gender_f" name="gender" value="f" <?php if(!empty($gender) && $gender == 'f') echo 'checked="checked"'; ?>><label for="gender_f">Female </label>
						</td>
					</tr>
					<tr>
						<th><label for="birthdate">Birth Date: </label></th>
						<td><input type="text" id="birthdate" name="birthdate" value="<?php if(!empty($birthdate)) echo $birthdate; ?>"></td>
					</tr>
					<tr>
						<th><label for="occupation">Occupation: </label></th>
						<td><input type="text" id="occupation" name="occupation" value="<?php if(!empty($occupation)) echo $occupation; ?>"></td>
					</tr>
					<tr>
						<th>Address: </th>
						<td>
							<table>
								<tr>
									<th><label for="line1">Line 1: </label></th>
									<td><input type="text" id="line1" name="line1" value="<?php if(!empty($line1)) echo $line1; ?>"></td>
								</tr>
								<tr>
									<th><label for="line2">Line 2: </label></th>
									<td><input type="text" id="line2" name="line2" value="<?php if(!empty($line2)) echo $line2; ?>"></td>
								</tr>
								<tr>
									<th><label for="city">City: </label></th>
									<td><input type="text" id="city" name="city" value="<?php if(!empty($city)) echo $city; ?>"></td>
								</tr>
								<tr>
									<th><label for="district">District: </label></th>
									<td><input type="text" id="district" name="district" value="<?php if(!empty($district)) echo $district; ?>"></td>
								</tr>
								<tr>
									<th><label for="state">State: </label></th>
									<td>
										<select id="state" name="state">
											<?php
												echo '<option value="0">Do not set.</option>'."\n";
												foreach($states as $currentstate) {
													if(!empty($state) && $state == $currentstate['state_id'])
														echo '<option value="'.$currentstate['state_id'].'" selected="selected">'.$currentstate['name'].'</option>'."\n";
													else
														echo '<option value="'.$currentstate['state_id'].'">'.$currentstate['name'].'</option>'."\n";
												}
											?>
										</select>
									</td>
								</tr>
								<tr>
									<th><label for="pincode">Pincode: </label></th>
									<td><input type="text" id="pincode" name="pincode" value="<?php if(!empty($pincode)) echo $pincode; ?>"></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<th><label for="email">Email: </label></th>
						<td><input type="text" id="email" name="email" value="<?php if(!empty($email)) echo $email; ?>"></td>
					</tr>
					<tr>
						<th><label for="phone">Phone: </label></th>
						<td><input type="text" id="phone" name="phone" value="<?php if(!empty($phone)) echo $phone; ?>"></td>
					</tr>
					<tr>
						<th><label for="picture">Picture: </label></th>
						<td>
							<input type="radio" id="upload_none" name="upload_type" value="0"><label for="upload_none">Do not update</label>
							<input type="radio" id="upload_file" name="upload_type" value="1"><label for="upload_file">Upload File</label>
							<input type="radio" id="upload_pic" name="upload_type" value="2"><label for="upload_pic">Take picture</label>
						</td>
					</tr>
					<tr id="camera-row">
						<td colspan="2">
							<?php require_once('../'.VC_INCLUDE.'takepic.php'); ?>
						</td>
					</tr>
					<tr id="file-row">
						<th></th>
						<td><input type="file" id="picture" name="picture"></td>
					</tr>
					<tr>
						<th></th>
						<td><input type="submit" id="submit" name="submit"><a href="<?php echo VC_LOCATION.'technician/'; ?>" class="back-link" title="Cancel">Cancel</a></td>
					</tr>
				</table>
			</form>
		</div>
	</div>

<?php require_once('../'.VC_INCLUDE.'footer.php');
