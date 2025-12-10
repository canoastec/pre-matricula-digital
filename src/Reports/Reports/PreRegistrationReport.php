<?php

namespace iEducar\Packages\PreMatricula\Reports\Reports;

use iEducar\Packages\PreMatricula\Reports\Querys\PMDNominalByPreRegistrationQuery;
use iEducar\Packages\PreMatricula\Reports\Querys\PMDPreRegistrationQuery;
use iEducar\Packages\PreMatricula\Reports\Querys\PMDQuantitativeByPreRegistrationQuery;
use iEducar\Packages\PreMatricula\Reports\Querys\PMDVacancyCertificateQuery;
use iEducar\Reports\Html;
use Portabilis_Report_ReportCore;

class PreRegistrationReport extends Portabilis_Report_ReportCore
{
    use Html;

    public const DETAILED_TEMPLATE = 1;

    public const SIMPLIFIED_TEMPLATE = 2;

    public const HTML_NOMINAL = 3;

    public const HTML_QUANTITATIVE = 4;

    public const VACANCY_CERTIFICATE = 5;

    public function templateName()
    {
        return match ((int) $this->args['template']) {
            self::HTML_NOMINAL => 'prematricula::reports.nominal',
            self::HTML_QUANTITATIVE => 'prematricula::reports.quantitative',
            self::VACANCY_CERTIFICATE => 'prematricula::reports.vacancy-certificate',
            self::DETAILED_TEMPLATE => 'prematricula::reports.detailed',
            default => 'prematricula::reports.simplified',
        };
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
    }

    public function useHtml()
    {
        return in_array($this->templateName(), [
            'prematricula::reports.simplified',
            'prematricula::reports.detailed',
            'prematricula::reports.nominal',
            'prematricula::reports.quantitative',
            'prematricula::reports.vacancy-certificate',
        ]);
    }

    public function getHtmlData()
    {
        $query = $this->getHtmlTemplate();

        return [
            'orientation' => 'portrait',
            'main' => (new $query($this->args))->get(),
        ];
    }

    public function getHtmlTemplate()
    {
        return match ((int) $this->args['template']) {
            self::HTML_NOMINAL => PMDNominalByPreRegistrationQuery::class,
            self::HTML_QUANTITATIVE => PMDQuantitativeByPreRegistrationQuery::class,
            self::VACANCY_CERTIFICATE => PMDVacancyCertificateQuery::class,
            default => PMDPreRegistrationQuery::class,
        };
    }
}
