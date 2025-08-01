<?php
include_once "../inc/sv.inc.php";
include "../inccon.php";
//include "../outputlib.php";

$time=time();
?>
<html>
<head>
<title>Tickets</title>
<?php include "cssinclude.php";?>
</head>
<body>
<div align="center">
<?php

include "det_userdata.inc.php";

if(isset($_REQUEST['showtid'])){
	$ticket_id=intval($_REQUEST['showtid']);
	$sql = "SELECT * FROM ls_tickets WHERE id=?";
	$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$ticket_id]);
	$num = mysqli_num_rows($db_daten);
	if($num>0){
		$row = mysqli_fetch_array($db_daten);
		$user_id=$row['user_id'];
		//�berpr�fen ob das ticket dem spieler geh�rt
			//�berpr�fen ob eine antwort eingef�gt werden soll
			if($_REQUEST['reply']==1)
			{
				$messagesql=trim($_REQUEST['nachricht']);
				//echo 'A: '.$messagesql;
				$messagesql=htmlspecialchars($messagesql, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
				//echo 'B: '.$messagesql;
				$messagesql=str_replace('\r\n', '<br>', $messagesql);

				$messagesql=$messagesql;
				//echo 'C: '.$messagesql;
				
				//nachricht hinterlegen
				$sql = "INSERT INTO ls_tickets_posts SET ticket_id=?, created=?, poster=?, message=?";
				mysqli_execute_query($GLOBALS['dbi'], $sql, [$ticket_id, $time, $det_email, $messagesql]);

				//ticketstatus anpassen
				$sql = "UPDATE ls_tickets SET modified=?, status=?, supporter=? WHERE id=?";
				mysqli_execute_query($GLOBALS['dbi'], $sql, [$time, 1, $det_email, $ticket_id]);
			}

			if($_REQUEST['close']==1)
			{
				//ticketstatus anpassen
				$sql = "UPDATE ls_tickets SET modified=?, status=?, supporter=? WHERE id=?";
				mysqli_execute_query($GLOBALS['dbi'], $sql, [$time, 1, $det_email, $ticket_id]);
				$row['status']=1;
			}				

			//nachricht ausgeben
			if($row['status']==0)$status='Ticket ist offen';else $status='Ticket ist geschlossen';
			echo '<div style="width: 640px; padding: 5px; background-color: #222222;">'.$row['thema'].' ('.$status.')</div>';

			//die einzelnen posts
			$sql = "SELECT * FROM ls_tickets_posts WHERE ticket_id=? ORDER BY created ASC";
			$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql, [$ticket_id]);
			$spielername='';
			while($row = mysqli_fetch_array($db_daten))
			{
				if($spielername=='')$spielername=$row['poster'];
				//header
				if($row['poster']==$spielername)
				{
					$bgcolor='#444444';
					echo '<div style="text-align:left; width: 640px; margin-top: 2px; padding: 5px; background-color: '.$bgcolor.';"><a href="http://login.bgam.es/ourdetool/idinfo.php?UID='.$user_id.'">'.$row['poster'].'</a> - '.date("G:i:s d.m.Y", $row['created']).'</div>';
				}
				else 
				{
					$bgcolor='#446644';
					echo '<div style="text-align:left; width: 640px; margin-top: 2px; padding: 5px; background-color: '.$bgcolor.';">'.$row['poster'].' - '.date("G:i:s d.m.Y", $row['created']).'</div>';
				}

				//body
				if($row['poster']==$spielername)$bgcolor='#222222';else $bgcolor='#226622';
				echo '<div style="text-align:left; width: 640px; margin-top: 1px; padding: 5px; background-color: '.$bgcolor.';">'.$row['message'].'</div>';
			}

			//antwortformular
			echo '<form action="tickets.php?reply=1&showtid='.$ticket_id.'" method="POST">';
			echo '<br>Nachricht:<br>';
			echo '<textarea rows="12" name="nachricht" cols="75"></textarea>'; 

			echo '<div align="center"><br><input type="submit" name="bieten" value="Nachricht senden"></div>';

			echo '</form>';

			echo '<br>Das Ticket ben&ouml;tigt keine Antwort: <a href="tickets.php?close=1&showtid='.$ticket_id.'">Ticket schlie&szlig;en</a>';
	}
}
else 
{
	echo '<div style="font-size: 20px;">Offene Tickets</div>';

		$sql = "SELECT * FROM ls_tickets WHERE status=0 ORDER BY created DESC";
		$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql);
    	$num = mysqli_num_rows($db_daten);
    	if($num>0)
    	{
    		//kopf
    		echo '<table width="100%">';
    		echo '<tr><td>Betreff</td><td>User</td><td>erstellt</td><td>letzte &auml;nderung</td><td>Supporter</td></tr>';
    	
    		while($row = mysqli_fetch_array($db_daten))
    		{
    			//spielernamen auslesen
    			$sql = "SELECT * FROM ls_user WHERE user_id=?";
    			$db_datenx = mysqli_execute_query($GLOBALS['dbi'], $sql, [$row['user_id']]);
    			$rowx = mysqli_fetch_array($db_datenx);
    			
    			echo '<tr>';
    			echo '<td><a href="tickets.php?showtid='.$row['id'].'">'.$row['thema'].'</a></td>';
    			echo '<td><a href="idinfo.php?UID='.$row['user_id'].'">'.$rowx['spielername'].'</a></td>';
    			echo '<td>'.date("H:i:s d.m.Y", $row['created']).'</td>';
    			echo '<td>'.date("H:i:s d.m.Y", $row['modified']).'</td>';
    			if($row['supporter']=='')$status='noch keiner';else $status=$row['supporter'];
    			echo '<td>'.$status.'</td>';
    		
    			echo '</tr>';
    		}
    	
    		echo '</table>';
    	}
    	else echo 'Es gibt keine offenen Tickets.';


echo '<br><hr><br>';

echo '<div style="font-size: 20px;">Beantwortete Tickets</div>';

		$sql = "SELECT * FROM ls_tickets WHERE status=1 ORDER BY created DESC LIMIT 50";
		$db_daten = mysqli_execute_query($GLOBALS['dbi'], $sql);
    	$num = mysqli_num_rows($db_daten);
    	if($num>0)
    	{
    		//kopf
    		echo '<table width="100%">';
    		echo '<tr><td>Betreff</td><td>Spieler</td><td>erstellt</td><td>letzte &auml;nderung</td><td>Supporter</td></tr>';
    	
    		while($row = mysqli_fetch_array($db_daten))
    		{
    			//spielernamen auslesen
    			$sql = "SELECT * FROM ls_user WHERE user_id=?";
    			$db_datenx = mysqli_execute_query($GLOBALS['dbi'], $sql, [$row['user_id']]);
    			$rowx = mysqli_fetch_array($db_datenx);
    			
    			echo '<tr>';
    			echo '<td><a href="tickets.php?showtid='.$row['id'].'">'.$row['thema'].'</a></td>';
    			echo '<td><a href="idinfo.php?UID='.$row['user_id'].'">'.$rowx['spielername'].'</a></td>';
    			echo '<td>'.date("H:i:s d.m.Y", $row['created']).'</td>';
    			echo '<td>'.date("H:i:s d.m.Y", $row['modified']).'</td>';
    			if($row['supporter']=='')$status='noch keiner';else $status=$row['supporter'];
    			echo '<td>'.$status.'</td>';
    		
    			echo '</tr>';
    		}
    	
    		echo '</table>';
    	}
    	else echo 'Es gibt keine beantworteten Tickets.';

}
?>
</div>
</body>
</html>