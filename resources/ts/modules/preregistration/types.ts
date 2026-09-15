import {
  Address,
  ErrorResponse,
  Nullable,
  PaginatorInfo,
  Rules,
} from '@/types';
import { ProcessStageRestriction } from '@/modules/processes/types';

export interface PreRegistrationOverview {
  id: number;
  protocol: string;
  code: string;
  type: string;
  date: string;
  position: number;
  school: {
    id: number;
    name: string;
    area_code: string;
    phone: string;
  };
  grade: {
    id: number;
    name: string;
  };
  period: {
    id: number;
    name: string;
  };
  process?: {
    showPriorityProtocol: boolean;
  };
}

export interface PreRegistrationResponsibleField
  extends PreRegistrationResponsible {
  relationType: Nullable<string>;
  address: Address;
  useSecondAddress: boolean;
  secondAddress: Address;
}

export interface Match {
  id: number;
  initials: string;
  dateOfBirth: string;
  type: ProcessStageRestriction;
  registration: {
    year: number;
    school: {
      id: number;
      name: string;
    };
    grade: {
      id: number;
      name: string;
    };
  };
}

export interface WaitingList {
  period: string;
  school: string;
}

export interface PreRegistrationStudentField extends PreRegistrationStudent {
  grade: Nullable<string>;
  period: Nullable<string>;
  school: Nullable<string>;
  useSecondSchool: Nullable<boolean>;
  secondSchool: Nullable<string>;
  secondPeriod: Nullable<string>;
  match: Nullable<Match>;
  waitingList: WaitingList[];
  responsible_name?: string;
  responsible_cpf?: string;
  responsible_date_of_birth?: string;
  responsible_phone?: string;
}

export interface PreRegistrationList {
  id: string;
  type: string;
  protocol: string;
  status: string;
  priority?: number;
  date?: string;
  student: {
    name: string;
    initials: string;
  };
  grade: {
    name: string;
  };
  period: {
    name: string;
  };
  school: {
    name: string;
  };
  schoolYear: {
    year: string;
  };
  position: number;
  waiting?: {
    id: string;
    type: string;
    status: string;
    position: number;
    student: {
      name: string;
    };
    period: {
      name: string;
    };
    school: {
      name: string;
    };
    schoolYear: {
      year: string;
    };
  };
  parent: {
    id: string;
    type: string;
    status: string;
    position: number;
    student: {
      name: string;
    };
    period: {
      name: string;
    };
    school: {
      name: string;
    };
    schoolYear: {
      year: string;
    };
  };
}

export interface StatProcesses {
  id?: string;
  accepted: number;
  rejected: number;
  total: number;
  vacancies: number;
  waiting: number;
}

export interface PreRegistrationFilter {
  template: string;
  protocol: string;
}

export interface Filter {
  first?: number;
  search: Nullable<string>;
  process: Nullable<string>;
  school: Nullable<string>;
  period?: Nullable<string>;
  grade: Nullable<string>;
  grades: Nullable<string[]>;
  type?: Nullable<string>;
  status?: Nullable<string>;
  sort: Nullable<string>;
  canAcceptInBatch: boolean;
  year: Nullable<number>;
  schools: Nullable<string[]>;
  processes: Nullable<string[]>;
}

export interface PreRegistrationStageProcessSchool {
  id: string;
  name: string;
  latitude: number;
  longitude: number;
}

export interface PreRegistrationStageProcessGrade {
  id: string;
  name: string;
  startBirth: string;
  endBirth: string;
}

export interface PreRegistrationStageProcessVacancy {
  school: number;
  period: number;
  grade: number;
  total: number;
  available: number;
}

export interface PreRegistrationStageProcessPeriod {
  id: string;
  name: string;
}

export interface PreRegistrationStageProcess {
  id: string;
  name: string;
  messageFooter: string;
  gradeAgeRangeLink: Nullable<string>;
  forceSuggestedGrade: boolean;
  showPriorityProtocol: boolean;
  schoolYear: {
    year: number;
  };
  grades: PreRegistrationStageProcessGrade[];
  periods: PreRegistrationStageProcessPeriod[];
  schools: PreRegistrationStageProcessSchool[];
  fields: ProcessField[];
  vacancies: PreRegistrationStageProcessVacancy[];
  allowResponsibleSelectMapAddress: boolean;
  blockIncompatibleAgeGroup: boolean;
  autoRejectByDays: boolean;
  autoRejectDays: Nullable<number>;
  onePerYear: boolean;
  waitingListLimit: number;
  minimumAge: Nullable<number>;
}

export interface PreRegistrationStage {
  id: string;
  renewalAtSameSchool: boolean;
  allowWaitingList: boolean;
  allowSearch: boolean;
  radius: number;
  type: string;
  status: string;
  restrictionType: ProcessStageRestriction;
  process: PreRegistrationStageProcess;
}

export interface PreRegistrationProcess {
  id: string;
  name: string;
  fields: ProcessField[];
  schoolYear: {
    year: number;
  };
  grades: {
    id: string;
    name: string;
  }[];
  periods: {
    id: string;
    name: string;
  }[];
  schools: {
    id: string;
    name: string;
    lat: string;
    lng: string;
  }[];
  vacancies: {
    school: string;
    period: string;
    grade: string;
    total: string;
    available: string;
  }[];
}

export interface ExternalPerson {
  id: number;
  name: string;
  gender: Nullable<number>;
  dateOfBirth: Nullable<string>;
  cpf: Nullable<string>;
  rg: Nullable<string>;
  birthCertificate: Nullable<string>;
  phone: Nullable<string>;
  mobile: Nullable<string>;
  email: Nullable<string>;
  address: Nullable<string>;
  number: Nullable<string>;
  complement: Nullable<string>;
  neighborhood: Nullable<string>;
  postalCode: Nullable<string>;
}

export interface PreRegistrationStudent {
  student_name: Nullable<string>;
  student_date_of_birth: string;
  student_cpf: string;
  student_rg: string;
  student_marital_status: number;
  student_place_of_birth: number | string;
  student_city_of_birth: string;
  student_birth_certificate: string;
  student_gender: number;
  student_email: string;
  student_phone: string;
  student_mobile: string;
}

export interface PreRegistrationResponsible {
  responsible_name: Nullable<string>;
  responsible_date_of_birth: string;
  responsible_cpf: string;
  responsible_rg: string;
  responsible_marital_status: number;
  responsible_place_of_birth: number | string;
  responsible_city_of_birth: string;
  responsible_gender: number;
  responsible_email: string;
  responsible_phone: string;
  responsible_mobile: string;
  responsible_address: {
    postalCode: string;
    address: string;
    number: string;
    neighborhood: string;
    city: string;
    complement: string;
    manualChangeLocation: boolean;
    lat: string;
    lng: string;
  }[];
}

export interface PreRegistration {
  id: number;
  date: string;
  protocol: string;
  status: string;
  type: string;
  observation: string;
  code?: string;
  grade: {
    id: string;
    name: string;
    course: {
      name: string;
    };
  };
  period: {
    id: string;
    name: string;
  };
  school: {
    id: string;
    name: string;
  };
  classroom: {
    id: string;
    name: string;
  };
  position: number;
  waiting: {
    id: string;
    protocol: string;
    school: {
      id: string;
      name: string;
    };
    position: number;
  };
  parent: {
    id: string;
    protocol: string;
    school: {
      id: string;
      name: string;
    };
    position: number;
  };
  others: {
    id: string;
    protocol: string;
    school: {
      id: string;
      name: string;
    };
    position: number;
  }[];
  student: PreRegistrationStudent;
  responsible: PreRegistrationResponsible;
  relationType: string;
  fields: {
    id: string;
    value: string;
    field: {
      id: string;
      name: string;
      internal: string;
      group: string;
    };
  }[];
  process: PreRegistrationProcess;
  inClassroom: Nullable<{
    id: string;
    name: string;
    period: {
      id: string;
      name: string;
    };
  }>;
  external: Nullable<ExternalPerson>;
}

export interface ProcessField {
  id: string;
  order: number;
  field: ProcessFieldField;
  required: string | number | boolean;
  weight: number;
}

export interface ProcessFieldField {
  id: string;
  name: string;
  description: Nullable<string>;
  group: string;
  internal:
    | 'responsible_rg'
    | 'student_rg'
    | 'responsible_name'
    | 'student_name'
    | 'responsible_date_of_birth'
    | 'student_date_of_birth';
  type:
    | 'CPF'
    | 'RG'
    | 'PHONE'
    | 'DATE'
    | 'TIME'
    | 'EMAIL'
    | 'CHECKBOX'
    | 'RADIO'
    | 'SELECT'
    | 'GENDER'
    | 'NUMBER'
    | 'MARITAL_STATUS'
    | 'CITY'
    | 'BIRTH_CERTIFICATE'
    | 'TEXT';
  options: {
    id: string;
    key: string;
    name: string;
    label: string;
    weight: number;
  }[];
}

export interface ParseFieldFromProcess extends ProcessField {
  id: string;
  key: string;
  rules: Rules;
  classes: {
    [key: string]: boolean;
  };
  type: string;
  label: string;
  filter:
    | ((value: string) => string)
    | ((value: string) => {
        key: string | Nullable<number>;
        label: string;
      });
}

export interface Fields {
  responsible: ParseFieldFromProcess[];
  student: ParseFieldFromProcess[];
}

export interface PreregistrationLoad {
  errors: boolean;
  stats: StatProcesses;
  preregistrations: PreRegistrationList[];
  paginator: PaginatorInfo;
}

export interface SchoolYear {
  year: number;
}

export interface ReportOptions {
  template: number;
  showStudentShortName: boolean;
  disregardStudentsIeducar: boolean;
}

export interface Period {
  id: number | string;
  name: string;
}

export interface Grade {
  id: string;
  key: string;
  label: string;
  name: string;
}

export interface Processes {
  id: string;
  key: string;
  label: string;
  name: string;
  schoolYear: SchoolYear;
  totalPreRegistrations: number;
}

export interface School {
  id: string;
  key: string;
  label: string;
  name: string;
}

export interface GetPreregistrations {
  grades: Grade[];
  periods: Period[];
  processes: Processes[];
  schools: School[];
}

export interface GetProcess {
  id: string;
  name: string;
  criteria: string;
  key: string;
  label: string;
  totalPreRegistrations: number;
  schoolYear: {
    year: number;
  };
  grades: Grade[];
  periods: Period[];
  schools: School[];
  fields: ProcessField[];
}

export interface PreregistrationSubmitInput {
  process: string;
  stage: string;
  type: string;
  grade: Nullable<string>;
  period: Nullable<string>;
  school: Nullable<string>;
  optionalSchool: Nullable<string>;
  optionalPeriod: Nullable<string>;
  relationType: Nullable<string>;
  address: Address;
  externalPerson: Nullable<number>;
  optionalAddress: Nullable<Address>;
  responsible: {
    field: string;
    value: Nullable<string>;
  }[];
  student: {
    field: string;
    value: Nullable<string>;
  }[];
  waitingList: WaitingList[];
}

export interface PreregistrationSubmit {
  errors?: ErrorResponse[];
  preregistrations: PreRegistrationOverview[];
}

export interface PreregistrationClassroom {
  key: number;
  label: string;
  period: {
    name: string;
  };
  available?: number;
}

export interface PreregistrationBatchResponse {
  id: string;
  student: {
    name: string;
  };
}

export interface PreregistrationBatchAccept {
  errors?: ErrorResponse[];
  acceptPreRegistrations: PreregistrationBatchResponse[];
}

export interface PreregistrationBatchReject {
  errors?: ErrorResponse[];
  rejectPreRegistrations: PreregistrationBatchResponse[];
}

export interface PreregistrationBatchSummon {
  errors?: ErrorResponse[];
  summonPreRegistrations: PreregistrationBatchResponse[];
}

export interface PreRegistrationBatchProps {
  filter: Filter;
  preregistrations: string[];
  step?: string;
  processYear: Nullable<number>;
  modelValue: boolean;
}
export interface StatProcessesProps {
  stats: StatProcesses;
}
