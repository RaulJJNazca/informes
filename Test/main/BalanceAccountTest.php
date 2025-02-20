<?php
/**
 * This file is part of Informes plugin for FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Test\Plugins;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Plugins\Informes\Model\BalanceAccount;
use FacturaScripts\Plugins\Informes\Model\BalanceCode;
use FacturaScripts\Test\Traits\LogErrorsTrait;
use PHPUnit\Framework\TestCase;

/**
 * Description of BalanceAccount
 *
 * @author Carlos Garcia Gomez     <carlos@facturascripts.com>
 * @author Daniel Ferández Giménez <hola@danielfg.es>
 */
final class BalanceAccountTest extends TestCase
{
    use LogErrorsTrait;

    public static function setUpBeforeClass(): void
    {
        $balanceCode = new BalanceCode();
        $balanceAccount = new BalanceAccount();
        $database = new DataBase();
        $database->updateSequence($balanceCode::tableName(), $balanceCode->getModelFields());
        $database->updateSequence($balanceAccount::tableName(), $balanceAccount->getModelFields());
    }

    public function testCreate()
    {
        // creamos un balance
        $balance = new BalanceCode();
        $balance->codbalance = 'TEST';
        $balance->nature = 'TEST NATURALEZA';
        $balance->subtype = 'test';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta para el balance anterior
        $accountBalance = new BalanceAccount();
        $accountBalance->idbalance = $balance->id;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');
        $this->assertNotNull($accountBalance->primaryColumnValue(), 'account-balance-not-stored');
        $this->assertTrue($accountBalance->exists(), 'account-balance-cant-persist');

        // eliminamos
        $this->assertTrue($accountBalance->delete(), 'account-balance-cant-delete');
        $this->assertTrue($balance->delete(), 'balance-cant-delete');
    }

    public function testAccountBalanceNoBalance()
    {
        // creamos un balance de cuenta sin balance
        $accountBalance = new BalanceAccount();
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';

        // no debe guardar
        $this->assertFalse($accountBalance->save(), 'account-balance-can-save-without-balance');
    }

    public function testHtmlOnFields()
    {
        // creamos un balance
        $balance = new BalanceCode();
        $balance->codbalance = 'TEST';
        $balance->nature = 'TEST';
        $balance->subtype = 'test';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta con html en los campos
        $accountBalance = new BalanceAccount();
        $accountBalance->idbalance = $balance->id;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = '<b>Test Html</b>';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');

        // comprobamos que el html ha sido escapado
        $noHtml = ToolBox::utils()::noHtml('<b>Test Html</b>');
        $this->assertEquals($noHtml, $accountBalance->desccuenta, 'account-balance-wrong-html');

        // eliminamos
        $this->assertTrue($accountBalance->delete(), 'account-balance-cant-delete');
        $this->assertTrue($balance->delete(), 'balance-cant-delete');
    }

    public function testDeleteCascade()
    {
        // creamos un balance
        $balance = new BalanceCode();
        $balance->codbalance = 'TEST';
        $balance->nature = 'TEST';
        $balance->subtype = 'test';
        $this->assertTrue($balance->save(), 'balance-cant-save');

        // creamos un balance de cuenta
        $accountBalance = new BalanceAccount();
        $accountBalance->idbalance = $balance->id;
        $accountBalance->codcuenta = 'TEST';
        $accountBalance->desccuenta = 'TEST DESCRIPTION';
        $this->assertTrue($accountBalance->save(), 'account-balance-cant-save');

        // eliminamos el balance
        $this->assertTrue($balance->delete(), 'balance-cant-delete');

        // comprobamos que no existe el balance de cuenta
        $this->assertFalse($accountBalance->exists(), 'account-balance-exists');
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
