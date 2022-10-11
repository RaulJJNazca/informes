<?php
/**
 * This file is part of Informes plugin for FacturaScripts
 * Copyright (C) 2017-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Plugins\Informes\Controller;

use FacturaScripts\Dinamic\Lib\Accounting\Ledger;
use FacturaScripts\Dinamic\Model\ReportLedger;
use FacturaScripts\Plugins\Informes\Lib\ExtendedController\EditReportAccounting;

/**
 * Description of EditReportLedger
 *
 * @author Carlos Garcia Gomez  <carlos@facturascripts.com>
 * @author Jose Antonio Cuello  <yopli2000@gmail.com>
 */
class EditReportLedger extends EditReportAccounting
{
    public function getModelClassName(): string
    {
        return 'ReportLedger';
    }

    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'reports';
        $data['title'] = 'ledger';
        $data['icon'] = 'fas fa-file-alt';
        return $data;
    }

    protected function createViews()
    {
        parent::createViews();

        // disable company column if there is only one company
        if ($this->empresa->count() < 2) {
            $this->views[$this->getMainViewName()]->disableColumn('company');
        }
    }

    /**
     * Generate Ledger data for report
     *
     * @param ReportLedger $model
     * @param string $format
     *
     * @return array
     */
    protected function generateReport($model, $format)
    {
        $params = [
            'channel' => $model->channel,
            'entry-from' => $model->startentry,
            'entry-to' => $model->endentry,
            'format' => $format,
            'grouped' => $model->groupingtype,
            'idcompany' => $model->idcompany,
            'subaccount-from' => $model->startcodsubaccount,
            'subaccount-to' => $model->endcodsubaccount
        ];

        $ledger = new Ledger();
        if (false === $ledger->setExerciseFromDate($model->idcompany, $model->startdate)) {
            $this->toolBox()->i18nLog()->warning('accounting-exercise-not-found');
            return [];
        }
        return $ledger->generate($model->startdate, $model->enddate, $params);
    }
}
