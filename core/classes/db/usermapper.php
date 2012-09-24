<?php
class UserMapper extends Mapper{
	
	public function add($user) {
		
		$statement = $this->db->prepare('insert into user set 
			email = :email, 
			password = :password, 
			status = :status, 
			registered = NOW()
		');
		
		$statement->execute(array(
			':email' => $user->getEmail(),
			':password' => $user->getPassword(),
			':status' => $user->getStatus(),
		));
	}
	
	public function update($user) {
		$statement = $this->db->prepare('update user set 
			email = :email,
			password = :password,
			status = :status
			where userId = :userId
		');
		
		$statement->execute(array(
			':email' => $user->getEmail(),
			':password' => $user->getPassword(),
			':status' => $user->getStatus(),
			':userId' => $user->getUserId(),
		));
	}
	
	public function delete($user) {
		$statement = $this->db->prepare('delete from user where userId = :userId');
		$statement->execute(array(
			':userId' => $user->getUserId(),
		));
	}
	
	public function get($user, $filter = array()) {
		$where = '';
		if(!empty($filter)) {

		}
		$statement = $this->db->prepare('select from user where ');
	}
	 
}

?>