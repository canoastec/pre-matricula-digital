import { Filter, PreRegistrationFilter } from '@/modules/preregistration/types';
import { rest } from '@/modules/preregistration/api';

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
