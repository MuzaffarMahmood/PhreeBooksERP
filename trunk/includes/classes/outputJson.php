<?php
namespace core\classes;
class outputJson implements \SplObserver{

	public function update(\SplSubject $basis) {
		global $messageStack;
		if($basis->page == 'json'){
			header('Content-Type: application/json');
			echo json_encode($basis);
			return true;
		}else{
			return false;
		}

	}
}
?>