<?php

namespace Littled\Tests\Account\DataProvider;


use Littled\Account\Address;

class AddressTestData
{
	public bool $expected;
	public string $msg;
	public Address $obj;

	public function __construct(
		bool $expected,
		?int $id=null,
		?string $first_name='',
		?string $last_name='',
		?string $address1='',
		?string $address2='',
		?string $city='',
		?int $state_id=null,
		?string $state='',
		?string $zip='',
		string $msg='')
	{
		$this->expected = $expected;
		$this->msg = $msg;
		$this->obj = new Address();
		$this->obj->id->value = $id;
		$this->obj->first_name->value = $first_name;
		$this->obj->last_name->value = $last_name;
		$this->obj->address1->value = $address1;
		$this->obj->address2->value = $address2;
		$this->obj->city->value = $city;
		$this->obj->state_id->value = $state_id;
		$this->obj->state->value = $state;
		$this->obj->zip->value = $zip;
	}
}