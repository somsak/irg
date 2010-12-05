<?php
class RRAGraphController{
	function getRRAs(){
		$sql = "SELECT * FROM `rra`";

		return db_fetch_assoc($sql);
	}

	function getRRAById($id){
		$sql = "SELECT * FROM `rra` WHERE `id` = {$id}";

		return db_fetch_row($sql);
	}
}
?>
