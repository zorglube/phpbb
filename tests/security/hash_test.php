<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

class phpbb_security_hash_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		global $phpbb_container;

		$config = new \phpbb\config\config(array());
		$phpbb_container = $this->createMock('Symfony\Component\DependencyInjection\ContainerInterface');
		$driver_helper = new \phpbb\passwords\driver\helper($config);
		$passwords_drivers = array(
			'passwords.driver.bcrypt_2y'	=> new \phpbb\passwords\driver\bcrypt_2y($config, $driver_helper),
			'passwords.driver.bcrypt'		=> new \phpbb\passwords\driver\bcrypt($config, $driver_helper),
			'passwords.driver.salted_md5'	=> new \phpbb\passwords\driver\salted_md5($config, $driver_helper),
			'passwords.driver.phpass'		=> new \phpbb\passwords\driver\phpass($config, $driver_helper),
		);

		$passwords_helper = new \phpbb\passwords\helper;
		// Set up passwords manager
		$passwords_manager = new \phpbb\passwords\manager($config, $passwords_drivers, $passwords_helper, array_keys($passwords_drivers));

		$phpbb_container
			->expects($this->any())
			->method('get')
			->with('passwords.manager')
			->will($this->returnValue($passwords_manager));
	}

	public function test_check_hash_with_phpass()
	{
		global $phpbb_container;

		/** @var \phpbb\passwords\manager $passwords_manager */
		$passwords_manager = $phpbb_container->get('passwords.manager');

		$this->assertTrue($passwords_manager->check('test', '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertTrue($passwords_manager->check('test', '$P$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertFalse($passwords_manager->check('foo', '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
	}

	public function test_check_hash_with_large_input()
	{
		global $phpbb_container;

		/** @var \phpbb\passwords\manager $passwords_manager */
		$passwords_manager = $phpbb_container->get('passwords.manager');

		// 16 MB password, should be rejected quite fast
		$start_time = time();
		$this->assertFalse($passwords_manager->check(str_repeat('a', 1024 * 1024 * 16), '$H$9isfrtKXWqrz8PvztXlL3.daw4U0zI1'));
		$this->assertLessThanOrEqual(5, time() - $start_time);
	}
}

