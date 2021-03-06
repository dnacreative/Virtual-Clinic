<?php
	function is_filetype_image($file) {
		$filetypes = array("image/jpeg", "image/pjpeg", "image/png", "image/bmp", "image/gif");
		foreach($filetypes as $filetype) {
			if($filetype == $file)
				return true;
		}
		return false;
	}

	function is_filetype_pdf($file) {
		$filetype = "application/pdf";
		if($filetype == $file)
			return true;
		return false;
	}

	$query_case = "SELECT complaint_id, altname, chronic, patient_history, past_history, personal_history, family_history, examination, DATE(date_created) AS date_created FROM vc_case WHERE case_id=".$case_id;
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) != 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$row_case = mysqli_fetch_array($data_case);

	$query_case = "SELECT complaint, chronic_only FROM vc_complaint WHERE complaint_id=".$row_case['complaint_id'];
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) != 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$row_temp = mysqli_fetch_array($data_case);
	$row_case['complaint'] = $row_temp['complaint'];
	$row_case['chronic_only'] = $row_temp['chronic_only'];

	if($row_case['complaint_id'] == VC_COMPLAINT_UNLISTED)
		$title = $row_case['altname'];
	else 
		$title = $row_case['complaint'];

	if($row_case['chronic'] == '0')
		$type = "Acute";
	else
		$type = "Chronic";

	$row_case['patient_history'] = preg_replace("/\r\n/", "<br>", $row_case['patient_history']);
	$row_case['past_history'] = preg_replace("/\r\n/", "<br>", $row_case['past_history']);
	$row_case['personal_history'] = preg_replace("/\r\n/", "<br>", $row_case['personal_history']);
	$row_case['family_history'] = preg_replace("/\r\n/", "<br>", $row_case['family_history']);
	$row_case['examination'] = preg_replace("/\r\n/", "<br>", $row_case['examination']);

	$date_arr = explode('-', $row_case['date_created']);
	$date_arr = array_reverse($date_arr);
	$row_case['date_created'] = implode('-', $date_arr);

	$query_case = "SELECT COUNT(*) AS count_test FROM vc_test WHERE case_id=".$case_id;
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) != 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$row_temp = mysqli_fetch_array($data_case);
	$row_case['count_test'] = $row_temp['count_test'];

	if($row_case['count_test'] > 0) {
		$query_case = "SELECT * FROM vc_test WHERE case_id=".$case_id;
		$data_case = mysqli_query($dbc, $query_case);
		if(mysqli_num_rows($data_case) < 1) {
			echo '<p class="error">Some error occured.</p>';
			exit();
		}
		$data_case_tests = array();
		while($row_temp = mysqli_fetch_array($data_case)) {
			array_push($data_case_tests, $row_temp);
		}
	}

	$query_case = "SELECT COUNT(*) AS count_treatment FROM vc_treatment WHERE case_id=".$case_id;
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) != 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$row_temp = mysqli_fetch_array($data_case);
	$row_case['count_treatment'] = $row_temp['count_treatment'];

	if($row_case['count_treatment'] > 0) {
		$query_case = "SELECT * FROM vc_treatment WHERE case_id=".$case_id;
		$data_case = mysqli_query($dbc, $query_case);
		if(mysqli_num_rows($data_case) < 1) {
			echo '<p class="error">Some error occured.</p>';
			exit();
		}
		$data_case_treatments = array();
		while($row_temp = mysqli_fetch_array($data_case)) {
			$temp_dosage = str_split($row_temp['dosage']);
			$row_temp['dosage'] = implode('-', $temp_dosage);
			array_push($data_case_treatments, $row_temp);
		}
	}

	$query_case = "SELECT COUNT(*) AS count_files FROM vc_case_file WHERE case_id=".$case_id;
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) != 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$row_temp = mysqli_fetch_array($data_case);
	$row_case['count_files'] = $row_temp['count_files'];

	if($row_case['count_files'] > 0) {
		$query_case = "SELECT title, filename FROM vc_case_file WHERE case_id=".$case_id;
		$data_case = mysqli_query($dbc, $query_case);
		if(mysqli_num_rows($data_case) < 1) {
			echo '<p class="error">Some error occured.</p>';
			exit();
		}
		$data_case_files = array();
		while($row_temp = mysqli_fetch_array($data_case)) {
			array_push($data_case_files, $row_temp);
		}
	}

	$query_case = "SELECT * FROM vc_test_name";
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) < 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$data_test_names = array();
	$i = 1;
	while($row_temp = mysqli_fetch_array($data_case)) {
		$data_test_names[$i] = $row_temp['name'];
		$i++;
	}

	$query_case = "SELECT initial, unit FROM vc_treatment_type";
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) < 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$data_treatment_types = array();
	$i = 1;
	while($row_temp = mysqli_fetch_array($data_case)) {
		$data_treatment_types[$i] = $row_temp;
		$i++;
	}

	$query_case = "SELECT * FROM vc_treatment_name";
	$data_case = mysqli_query($dbc, $query_case);
	if(mysqli_num_rows($data_case) < 1) {
		echo '<p class="error">Some error occured.</p>';
		exit();
	}
	$data_treatment_names = array();
	$i = 1;
	while($row_temp = mysqli_fetch_array($data_case)) {
		$data_treatment_names[$i] = $data_treatment_types[$row_temp['treatment_type_id']]['initial'].'. '.$row_temp['name'].' '.substr($row_temp['strength'], 0, 5).$data_treatment_types[$row_temp['treatment_type_id']]['unit'];
		$i++;
	}

	$query_case = "SELECT assigneduser_id FROM vc_user WHERE user_id=".$_SESSION['user_id'];
	$data_case = mysqli_query($dbc, $query_case);
	$row_temp = mysqli_fetch_array($data_case);
	if(!empty($row_temp['assigneduser_id'])) {
		$notassigned = false;
		$row_case['assigneduser_id'] = $row_temp['assigneduser_id'];
		if($_SESSION['type'] == VC_TECHNICIAN)
			$query_case = "SELECT forward_id, status FROM vc_forward WHERE case_id=".$case_id." AND user_id=".$_SESSION['user_id'];
		else			
			$query_case = "SELECT forward_id, status FROM vc_forward WHERE case_id=".$case_id." AND user_id=".$row_temp['assigneduser_id'];
		$data_case = mysqli_query($dbc, $query_case);
		if(mysqli_num_rows($data_case) == 1) {
			$row_temp = mysqli_fetch_array($data_case);
			$row_case['status'] = $row_temp['status'];
			$row_case['forward_id'] = $row_temp['forward_id'];
		}
		else 
			$row_case['status'] = null;
	}
	else
		$notassigned = true;
?>
<div id="case" data-case-id="<?php echo $case_id; ?>" data-case-user="<?php echo $_SESSION['user_id']; ?>">
	<?php if(!$notassigned) { ?>
	<?php if(is_null($row_case['status'])) { ?>
	<?php if($_SESSION['type'] == VC_TECHNICIAN) { ?>
	<a id="forward-link" class="case-forward" title="Forward to doctor" href="#" data-user-id="<?php echo $_SESSION['user_id']; ?>" data-assigned="<?php echo $row_case['assigneduser_id']; ?>">Forward to doctor</a>
	<?php } } else if($row_case['status'] == 0) { ?>
	<?php if($_SESSION['type'] == VC_TECHNICIAN) { ?>
	<span class="case-under-review">Under Review</span>
	<?php } else { ?>
	<a id="forward-link" class="case-forward" title="Mark as complete" href="#" data-status="<?php echo $row_case['status']; ?>" data-user-id="<?php echo $_SESSION['user_id']; ?>" data-assigned="<?php echo $row_case['assigneduser_id']; ?>" data-forward-id="<?php echo $row_case['forward_id']; ?>">Mark as reviewed</a>
	<?php } } else { ?>
	<?php if($_SESSION['type'] == VC_TECHNICIAN) { ?>
	<div class="case-forward"><span class="case-reviewed">Reviewed</span> (<a id="forward-link" title="Dismiss" href="#" data-status="<?php echo $row_case['status']; ?>" data-user-id="<?php echo $_SESSION['user_id']; ?>" data-forward-id="<?php echo $row_case['forward_id']; ?>">x</a>)</div>
	<?php } } ?>
	<?php } ?> 
	<h3 id="case-heading"><?php if(isset($case_no)) echo '#'.$case_no.' '; echo $title; ?></h3>
	(<a id="case-edit" title="Edit Case" href="editcase.php?case_id=<?php echo $case_id; ?>&amp;patient_id=<?php echo $patient_id; ?>">Edit</a>)
	<div id="case-content">
		<table>
			<tr>
				<th>Date Created: </th>
				<td><?php echo $row_case['date_created']; ?></td>
			</tr>
			<tr>
				<th>Type: </th>
				<td><?php echo $type; ?></td>
			</tr>
		</table>
		<hr>
		<table>
			<tr>
				<th>Patient History: </th>
				<td><?php if(empty($row_case['patient_history'])) echo '<span class="nulldata">Not entered.</span>'; else echo $row_case['patient_history']; ?></td>
			</tr>
			<tr>
				<th>Past History: </th>
				<td><?php if(empty($row_case['past_history'])) echo '<span class="nulldata">Not entered.</span>'; else echo $row_case['past_history']; ?></td>
			</tr>
			<tr>
				<th>Personal History: </th>
				<td><?php if(empty($row_case['personal_history'])) echo '<span class="nulldata">Not entered.</span>'; else echo $row_case['personal_history']; ?></td>
			</tr>
			<tr>
				<th>Family History: </th>
				<td><?php if(empty($row_case['family_history'])) echo '<span class="nulldata">Not entered.</span>'; else echo $row_case['family_history']; ?></td>
			</tr>
		</table>
		<br>
		<hr>
		<br>
		<table>
			<tr>
				<th>Examination: </th>
				<td><?php if(empty($row_case['examination'])) echo '<span class="nulldata">Not entered.</span>'; else echo $row_case['examination']; ?></td>
			</tr>
		</table>
		<br>
		<hr>
		<br>
		<table>
			<tr>
				<th>Attached Files: </th>
				<td>
					<?php
						if($_SESSION['type'] == VC_TECHNICIAN) { 
							echo '<a class="add" id="add-file" title="Add File" href="#">Add File</a>';
							echo '<span> /</span><a class="add" id="add-photo" title="Take Photo" href="#">Take Photo</a><br>';
						}
						if(($row_case['count_files'] == "0")) {
							if(($_SESSION['type'] == VC_DOCTOR))
								echo '<span class="add nulldata">No files attached.</span><br>';
						}
						else { 
					?>
					<?php if($_SESSION['type'] == VC_TECHNICIAN) echo '<br>'; ?>
					<table id="files">
						<tr id="heading-files">
							<th id="width-8">Name</th>
							<th id="width-9">File</th>
						</tr>
						<?php $color = false; ?>
						<?php foreach($data_case_files as $data_case_file) { ?>
						<tr<?php if($color) echo ' class="color-row"'; ?>>
							<?php $color = !$color; ?>
							<td><?php echo $data_case_file['title']; ?></td>
							<td>
								<?php
									$filename = $_SERVER['DOCUMENT_ROOT'].'/vclinic/cases/case-'.$case_id.'/'.$data_case_file['filename'];
									$finfo = finfo_open(FILEINFO_MIME_TYPE);
									$filetype = finfo_file($finfo, $filename);
									finfo_close($finfo);
									$href = VC_LOCATION.'cases/case-'.$case_id.'/'.$data_case_file['filename'];
									if(is_filetype_image($filetype)) {
										echo '<a class="fancybox" href="'.$href.'">View</a>';
									}
									else if(is_filetype_pdf($filetype)) {
										echo '<a href="'.$href.'" target="_blank">Open</a>';
									}
									else {
										echo '<a href="'.$href.'" target="_blank">Download</a>';
									}
								?>
							</td>
						<?php } ?>
					</table>
					<?php } ?>
				</td>
			</tr>
		</table>
		<br>
		<hr>
		<br>
		<table>
			<tr>
				<th>Tests:</th>
				<td>
					<?php
						if($_SESSION['type'] == VC_TECHNICIAN) echo '<a class="add" id="add-test" title="Add Test" href="#">Add Test</a><br>';
						if(($row_case['count_test'] == "0")) {
							if(($_SESSION['type'] == VC_DOCTOR))
								echo '<span class="add nulldata">No tests conducted.</span><br>';
						}
						else { 	
					?>
					<?php if($_SESSION['type'] == VC_TECHNICIAN) echo '<br>'; ?>
					<table id="results">
						<tr id="heading-results">
							<th id="width-1">Name</th>
							<th id="width-2">Result</th>
							<th id="width-3">File</th>
						</tr>
						<?php $color = false; ?>
						<?php foreach($data_case_tests as $data_case_test) { ?>
						<tr<?php if($color) echo ' class="color-row"'; ?>>
							<?php $color = !$color; ?>
							<td><?php if($data_case_test['test_name_id'] == VC_TEST_UNLISTED) echo $data_case_test['altname']; else echo $data_test_names[intval($data_case_test['test_name_id'])]; ?></td>
							<td><?php echo $data_case_test['result']; ?></td>
							<td>
								<?php
									if(!empty($data_case_test['filename'])) {
										$filename = $_SERVER['DOCUMENT_ROOT'].'/vclinic/cases/case-'.$case_id.'/'.$data_case_test['filename'];
										$finfo = finfo_open(FILEINFO_MIME_TYPE);
										$filetype = finfo_file($finfo, $filename);
										finfo_close($finfo);
										$href = VC_LOCATION.'cases/case-'.$case_id.'/'.$data_case_test['filename'];
										if(is_filetype_image($filetype)) {
											echo '<a class="fancybox" href="'.$href.'">View</a>';
										}
										else if(is_filetype_pdf($filetype)) {
											echo '<a href="'.$href.'" target="_blank">Open</a>';
										}
										else {
											echo '<a href="'.$href.'" target="_blank">Download</a>';
										}
									}
									else 
										echo '<span>-</span>';
								?>
							</td>
						</tr>
						<?php } ?>
					</table>
					<?php } ?>
				</td>
			</tr>
		</table>
		<br>
		<hr>
		<br>
		<table>	
			<tr>
				<th>Treatment: </th>
				<td>
					<?php 
						echo '<a class="add" id="add-treatment" title="Add Prescription" href="#">Add Prescription</a><br><br>';
						if($row_case['count_treatment'] != "0") {
					?>
					<table id="treatments">
						<tr id="heading-treatments">
							<th id="width-4">Medicine</th>
							<th id="width-5">Dosage</th>
							<th id="width-6">Intake</th>
							<th id="width-7">Length</th>
						</tr>
						<?php $color = false; ?>
						<?php foreach($data_case_treatments as $data_case_treatment) { ?>
						<tr<?php if($color) echo ' class="color-row"'; ?>>
							<?php $color = !$color; ?>
							<td><?php if($data_case_treatment['treatment_name_id'] == VC_TREATMENT_UNLISTED) echo $data_case_treatment['altname']; else echo $data_treatment_names[intval($data_case_treatment['treatment_name_id'])]; ?></td>
							<td><?php echo $data_case_treatment['dosage']; ?></td>
							<td><?php if($data_case_treatment['before_food'] == "0") echo 'A/F'; else echo 'B/F'; ?></td>
							<td><?php echo $data_case_treatment['duration']; ?></td>
						</tr>
						<?php } ?>
					</table>
					<?php } ?>
				</td>
			</tr>
		</table>
		<br>
	</div>
</div>
