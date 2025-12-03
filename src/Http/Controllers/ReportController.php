<?php

namespace iEducar\Packages\PreMatricula\Http\Controllers;

use App\Models\LegacyInstitution;
use iEducar\Packages\PreMatricula\Reports\Reports\PreRegistrationReport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @codeCoverageIgnore
 */
class ReportController
{
    public function preRegistrationReport(Request $request)
    {
        $institution = LegacyInstitution::query()->where('ativo', 1)->firstOrFail();
        $report = new PreRegistrationReport;

        $report->addArg('instituicao', $institution->getKey());
        $report->addArg('institution', $institution->only([
            'id',
            'nm_instituicao',
            'nm_responsavel',
            'telefone',
            'ddd_telefone',
            'logradouro',
            'numero',
            'bairro',
            'cidade',
            'ref_sigla_uf',
            'cep',
            'altera_atestado_para_declaracao',
        ]));
        $report->addArg('ano', $request->query('year'));
        $report->addArg('escola', $request->query('school'));
        $report->addArg('type', $request->query('type'));
        $report->addArg('situacao', $request->query('status'));
        $report->addArg('turno', $request->query('period'));
        $report->addArg('processos', $request->query('processes'));
        $report->addArg('series', $request->query('grades'));
        $report->addArg('msg_rodape', _cl('report.candidato_fila_unica.rodape'));
        $report->addArg('template', $request->query('template'));
        $report->addArg('showStudentShortName', $request->query('showStudentShortName'));
        $report->addArg('disregardStudentsIeducar', $request->query('disregardStudentsIeducar'));
        $report->addArg('protocolo', $request->query('protocol'));

        $result = $report->dumps();

        if (!$result) {
            return new Response([], 500);
        }

        return new Response($result, 200, [
            'Content-Encoding' => 'none',
            'Content-Type' => 'application/pdf;',
            'Content-Disposition' => 'attachment; filename="pre-matricula.pdf"',
            'Content-Description' => 'Pré-matrícula',
        ]);
    }
}
