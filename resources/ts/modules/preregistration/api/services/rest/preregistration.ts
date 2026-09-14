import { Filter, PreRegistrationFilter } from '@/modules/preregistration/types';
import { http, rest } from '@/modules/preregistration/api';

export const toExport = (filter: Filter): Promise<Blob> =>
  rest<{
    data: Blob;
  }>('get', '/pre-matricula-export', {
    params: filter,
    responseType: 'blob',
  }).then((res) => res.data);

export const toReport = (filters: Filter): Promise<Blob> =>
  rest<{
    data: Blob;
  }>('get', '/pre-matricula-report', {
    params: filters,
    responseType: 'blob',
  }).then((res) => res.data);

export const toPreRegistrationReport = (
  filters: PreRegistrationFilter
): Promise<Blob> =>
  rest<{
    data: Blob;
  }>('get', '/pre-matricula-report', {
    params: filters,
    responseType: 'blob',
  }).then((res) => res.data);

export const toSiecImport = (
  file: File,
  processId: string | number,
  schoolId: string | number,
  gradeId: string | number
): Promise<{ imported: number; skipped: number; errors: Array<{ line: number; protocol?: string; message: string }> }> => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('process_id', String(processId));
  formData.append('school_id', String(schoolId));
  formData.append('grade_id', String(gradeId));

  const xsrf = document.cookie
    .split('; ')
    .find((row) => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1];

  return http
    .post('/pre-matricula-siec-import', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        ...(xsrf ? { 'X-XSRF-TOKEN': decodeURIComponent(xsrf) } : {}),
      },
    })
    .then((res) => res.data);
};
