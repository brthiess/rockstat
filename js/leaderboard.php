<?php
try {

	$con = new PDO('mysql:host=localhost;dbname=petrinary', "root", "jikipol");
	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
	$result = $con->query('SELECT * FROM admin');
	$result->setFetchMode(PDO::FETCH_ASSOC);
	foreach($result as $row) {
		foreach($row as $name=>$value) {
			print "<p>$name: $value</p>";
		}
	}
}
catch(PDOException $e) {
	echo 'Error: ' . $e.getMessage();
}

print <<<HERE
<table id="leaderboard-table-mens" class="tablesorter" border="0" cellpadding="0" cellspacing="1"> 
							<thead> 
								<tr> 
									<th>Team</th> 
									<th>Order of Merit</th> 
									<th>CTRS (pts)</th> 
									<th>Money (\$CDN)</th> 
									<th>?</th> 
								</tr> 
							</thead> 
							<tbody> 
								<tr> 
									<td>Jennifer Jones</td> 
									<td>1</td> 
									<td>400.5</td> 
									<td>\$50.00</td> 
									<td>http://www.jsmith.com</td> 
								</tr>
								<tr> 
									<td>Kevin Martin</td> 
									<td>3</td> 
									<td>400.5</td> 
									<td>\$50.00</td> 
									<td>http://www.jsmith.com</td> 
								</tr>
								<tr> 
									<td>Brad Jacobs</td> 
									<td>4</td> 
									<td>400.5</td> 
									<td>\$50.00</td> 
									<td>http://www.jsmith.com</td> 
								</tr>
								<tr> 
									<td>Rachel Homan</td> 
									<td>2</td> 
									<td>256.5</td> 
									<td>\$1000.00</td> 
									<td>http://www.jsmith.com</td> 
								</tr>
							</tbody> 
						</table> 	
HERE;
?>