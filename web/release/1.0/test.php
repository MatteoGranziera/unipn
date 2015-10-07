
<h2>Test Calendar unipn</h2>
<?php 
	include 'calendar.php';
	?>
	<h4>Date: 7/10/2015 - Area:B</h4>
	<?php
	print_r(GetInformations(7, 10, 2015, 4));
	?>
	<br>
	<h4>Date: 7/10/2015 - Area:S</h4>
	<?php
	print_r(GetInformations(7, 10, 2015, 5));
?>