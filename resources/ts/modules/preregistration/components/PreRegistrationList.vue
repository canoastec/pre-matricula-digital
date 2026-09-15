<template>
  <main>
    <h1>Pré-matrículas</h1>
    <stats-cards :stats="stats" />
    <x-card class="bg-primary mb-5">
      <x-card-section class="pl-2 pr-2">
        <form @submit.prevent="load(1)">
          <div class="row m-0 mt-1 filter-gutters mb-md-3">
            <x-field
              v-model="filter.search"
              container-class="col-12 col-md-4 mb-2 mb-md-0"
              name="search"
              placeholder="Nome do(a) aluno(a)"
              type="TEXT"
            />
            <x-field
              v-model="filter.year"
              container-class="col-12 col-md-2 mb-2 mb-md-0"
              name="year"
              placeholder="Filtrar ano"
              type="SELECT"
              :options="years"
            />
            <div class="col-12 col-md-3 mb-2 mb-md-0">
              <multi-select-custom
                v-model="filter.processes"
                :options="filteredProcesses"
                placeholder="Filtrar por processo"
                @change="filter.canAcceptInBatch = false"
              />
            </div>
            <x-field
              v-model="filter.type"
              container-class="col-12 col-md-3 mb-2 mb-md-0"
              name="type"
              placeholder="Filtrar por tipo"
              type="SELECT"
              :options="getTypes"
            />
          </div>
          <div class="row m-0 filter-gutters mt-md-4">
            <x-field
              v-model="filter.school"
              container-class="col-12 col-md-3 mb-2 mb-md-0"
              name="school"
              placeholder="Filtrar por escola"
              type="SELECT"
              :options="schools"
              searchable
              @change="filter.canAcceptInBatch = false"
            />
            <div class="col-12 col-md-3 mb-2 mb-md-0">
              <multi-select-custom
                v-model="filter.grades"
                container-class="col-12 col-md-3 mb-2 mb-md-0"
                :options="filteredGrades"
                placeholder="Filtrar por série"
                @change="filter.canAcceptInBatch = false"
              />
            </div>
            <x-field
              v-model="filter.period"
              container-class="col-12 col-md-3 mb-2 mb-md-0"
              name="period"
              placeholder="Filtrar por turno"
              type="SELECT"
              :options="getPeriods"
            />
            <x-field
              v-model="filter.status"
              container-class="col-12 col-md-3 mb-2 mb-md-0"
              name="status"
              placeholder="Filtrar por status"
              type="SELECT"
              :options="getStatuses"
            />
          </div>
          <div class="d-flex m-0 mt-3 filter-gutters flex-column flex-lg-row">
            <div class="d-flex flex-grow-1">
              <x-btn
                label="Emitir relatório"
                color="white"
                outline
                class="ml-2"
                no-caps
                no-wrap
                icon="mdi-note-text-outline"
                loading-normal
                @click="showModalReportOptions = true"
              />
              <x-btn
                label="Importar SIEC"
                color="white"
                outline
                class="ml-2"
                no-caps
                no-wrap
                icon="mdi-file-upload-outline"
                loading-normal
                @click="openSiecImportModal"
              />
              <x-btn
                v-if="
                  filter.processes?.length === 1 &&
                  filter.school &&
                  filter.grades?.length === 1 &&
                  filter.period &&
                  filter.status === 'WAITING'
                "
                label="Convocar próxima inscrição"
                color="white"
                outline
                class="ml-2"
                no-caps
                no-wrap
                loading-normal
                :loading="loadingNextInLine"
                @click="summonNextInLine"
              />
            </div>
            <div class="d-flex justify-content-end flex-grow-1 mt-2 mt-md-0">
              <x-btn
                data-test="cleanFilter"
                label="Limpar"
                color="white"
                flat
                class="mr-3"
                no-caps
                no-wrap
                @click="cleanFilter"
              />
              <x-btn
                data-test="btn-search-pre-registration"
                label="Buscar"
                color="white"
                outline
                class="mr-2"
                no-caps
                no-wrap
                :loading="loadingTable"
                loading-normal
                @click="load(1)"
              />
            </div>
          </div>
        </form>
      </x-card-section>
    </x-card>

    <div class="d-flex flex-column flex-lg-row mb-4">
      <div class="mb-3 mb-lg-0">
        <simple-select
          v-model="filter.sort"
          :options="sortPreRegistrations()"
          class="custom-select"
          @change="load(1)"
        >
          <template #option>
            <option :value="null">Ordenar por:</option>
          </template>
        </simple-select>
      </div>
      <div class="ml-lg-auto">
        <x-btn
          data-test="summon"
          label="Convocar responsáveis"
          :disable="summonDisabled"
          color="summon"
          outline
          class="ml-0 ml-lg-3 mb-2 mb-lg-0 pmd-custom"
          icon="pmd-summon"
          no-caps
          no-wrap
          @click="summon"
        />
        <x-btn
          data-test="reject"
          label="Indeferir pré-matrícula"
          :disable="rejectDisabled"
          color="rejected"
          outline
          class="ml-0 ml-lg-3 mb-2 mb-lg-0 pmd-custom"
          icon="pmd-rejected"
          no-caps
          no-wrap
          @click="reject"
        />
        <x-btn
          data-test="accept"
          label="Deferir pré-matrícula"
          :disable="acceptDisabled"
          color="accepted"
          outline
          class="ml-0 ml-lg-3 mb-2 mb-lg-0 pmd-custom"
          icon="pmd-accepted"
          no-caps
          no-wrap
          @click="accept"
        />
      </div>
    </div>
    <div class="align-items-baseline d-flex font-hind mb-2">
      <div class="mr-2">Mostrar</div>
      <div class="mr-2">
        <simple-select
          v-model="limitTableRows"
          :options="limitTableRowsOptions"
          class="form-control form-control-sm"
        />
      </div>
      <div>registros por página</div>
    </div>

    <skeleton-table v-if="loadingTable" />

    <table v-else class="table table-responsive table-striped table-vcenter">
      <thead>
        <tr>
          <th class="w-1">
            <div class="custom-control custom-checkbox">
              <input
                id="allChecked"
                v-model="allChecked"
                type="checkbox"
                class="custom-control-input"
                @change="checkAll"
              />
              <label class="custom-control-label" for="allChecked">
                <span class="d-none">Marcar todos</span>
              </label>
            </div>
          </th>
          <th>Status</th>
          <th>Protocolo</th>
          <th>Nome do(a) aluno(a)</th>
          <th>Escola</th>
          <th>Pontuação</th>
          <th>Posição</th>
          <th>Série</th>
          <th>Turno</th>
          <th>Tipo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="noData">
          <td colspan="10">Nenhum resultado.</td>
        </tr>
        <template v-else>
          <template
            v-for="row in preregistrationRows"
            :key="row.key"
          >
            <tr v-if="row.type === 'month'" class="table-month-group">
              <td colspan="10" class="font-weight-bold text-uppercase">
                {{ row.label }}
              </td>
            </tr>
            <tr v-else>
              <td class="w-1">
                <div class="custom-control custom-checkbox">
                  <input
                    :id="`checkbox-${row.item.id}`"
                    v-model="checked"
                    :value="row.item.id"
                    type="checkbox"
                    class="custom-control-input"
                    @change="checkOne"
                  />
                  <label
                    :for="`checkbox-${row.item.id}`"
                    class="custom-control-label"
                  >
                    <span class="d-none">Marcar pré-matrícula</span>
                  </label>
                </div>
              </td>
              <td>
                <span
                  v-tooltip.bottom-start="
                    preRegistrationStatusText(row.item.status)
                  "
                  :class="preRegistrationStatusClass(row.item.status)"
                ></span>
              </td>
              <td>
                <a
                  data-test="protocol"
                  href="javascript:void(0)"
                  @click="showPreRegistration(row.item.protocol)"
                >
                  #{{ row.item.protocol }}
                </a>
              </td>
              <td>
                {{ row.item.student.name }}
              </td>
              <td>
                {{ row.item.school.name }}
                <div v-if="row.item.waiting">
                  <small class="align-top">
                    {{ row.item.waiting.school.name }}
                  </small>
                  <span
                    :class="
                      preRegistrationStatusClass(row.item.waiting.status)
                    "
                  ></span>
                </div>
                <div v-if="row.item.parent">
                  <small class="align-top">
                    {{ row.item.parent.school.name }}
                  </small>
                  <span
                    :class="
                      preRegistrationStatusClass(row.item.parent.status)
                    "
                  ></span>
                </div>
              </td>
              <td>
                <span class="badge badge-secondary">
                  {{ row.item.priority || 0 }} PTS
                </span>
              </td>
              <td>
                {{ row.item.position }}º
                <div v-if="row.item.waiting">
                  <small class="align-top">
                    {{ row.item.waiting.position }}º
                  </small>
                </div>
                <div v-if="row.item.parent">
                  <small class="align-top">
                    {{ row.item.parent.position }}º
                  </small>
                </div>
              </td>
              <td>
                {{ row.item.grade ? row.item.grade.name : '-' }}
              </td>
              <td>
                {{ row.item.period ? row.item.period.name : '-' }}
              </td>
              <td>
                <span :class="badgeType(row.item.type)" class="badge">
                  {{ getType(row.item.type) }}
                </span>
              </td>
            </tr>
          </template>
        </template>
      </tbody>
    </table>
    <pagination :paginator-info="paginator" @page="load($event as number)" />
    <div
      v-if="paginator && paginator.total"
      class="row justify-content-center mt-4"
    >
      <div class="col-12 col-lg-3">
        <x-btn
          data-test="toExport"
          label="Exportar"
          :loading="loading"
          class="w-100"
          color="primary"
          no-caps
          no-wrap
          loading-normal
          @click="ctx.toExport()"
        />
      </div>
    </div>
    <pre-registration-batch
      v-if="showBatchModal"
      v-model="showBatchModal"
      :filter="filter"
      :preregistrations="checked"
      :process-year="processYear"
      :step="step"
      @load="load"
    />
    <modal-component
      ref="fieldsModal"
      v-model="showModalReportOptions"
      no-footer
      title="Emitir Relatório"
      is-overflow-visible
    >
      <template #body>
        <div class="row">
          <x-field
            id="reportModel"
            v-model="reportOptions.template"
            label="Modelo"
            rules="required"
            name="reportModel"
            type="SELECT"
            :allow-clear="false"
            :searchable="false"
            :options="reportsTemplates()"
            placeholder="Selecione o modelo"
            container-class="col-12 form-group"
            @open:options="fieldsModal.setOverflowVisible(true)"
            @close:options="fieldsModal.setOverflowVisible(false)"
          />
          <x-field
            v-model="reportOptions.showStudentShortName"
            name="showStudentShortName"
            container-class="col-12 form-group"
            label="Exibir apenas iniciais do nome do candidato?"
            type="CHECKBOX"
          />
          <x-field
            v-if="
              (reportOptions.template === 3 || reportOptions.template === 4) &&
              filter.status === 'WAITING'
            "
            v-model="reportOptions.disregardStudentsIeducar"
            v-tooltip.start-bottom="
              'Marque esta opção se, além de o relatório considerar apenas os alunos com situação \'Em espera\', também seja necessária uma validação no i-Educar para também desconsiderar alunos com matricula ativa no ano do processo'
            "
            name="disregardStudentsIeducar"
            container-class="col-12 form-group"
            label="Desconsiderar alunos com matrícula ativa no i-Educar?"
            type="CHECKBOX"
          />
        </div>
      </template>
      <template #footer>
        <div class="row">
          <div class="col-12">
            <x-btn
              data-test="toReport"
              :loading="loadingReport"
              color="primary"
              class="w-100"
              label="Emitir relatório"
              no-caps
              no-wrap
              @click="toReport"
            />
          </div>
        </div>
      </template>
    </modal-component>

    <modal-component
      v-model="showModalSiecImport"
      no-footer
      title="Importar SIEC"
      is-overflow-visible
    >
      <template #body>
        <div class="row">
          <x-field
            v-model="siecImport.processId"
            container-class="col-12 mb-3"
            name="siecProcess"
            label="Processo"
            type="SELECT"
            :options="siecProcessOptions"
            searchable
          />
          <x-field
            v-model="siecImport.schoolId"
            container-class="col-12 mb-3"
            name="siecSchool"
            label="Escola"
            type="SELECT"
            :options="siecSchoolOptions"
            searchable
          />
          <x-field
            v-model="siecImport.gradeId"
            container-class="col-12 mb-3"
            name="siecGrade"
            label="Série"
            type="SELECT"
            :options="siecGradeOptions"
            searchable
          />
          <div class="col-12 mb-3">
            <label class="form-label" for="siec-file">Arquivo CSV</label>
            <input
              id="siec-file"
              type="file"
              class="form-control"
              accept=".csv,text/csv"
              @change="onSiecFileChange"
            />
          </div>
        </div>
      </template>
      <template #footer>
        <div class="row">
          <div class="col-12">
            <x-btn
              data-test="toSiecImport"
              :loading="loadingSiecImport"
              :disable="
                !siecImport.processId ||
                !siecImport.schoolId ||
                !siecImport.gradeId ||
                !siecImport.file
              "
              color="primary"
              class="w-100"
              label="Importar"
              no-caps
              no-wrap
              @click="importSiec"
            />
          </div>
        </div>
      </template>
    </modal-component>

    <router-view v-slot="{ Component }">
      <component :is="Component" :key="route.path" />
    </router-view>
  </main>
</template>

<script setup lang="ts">
import { AppContext, computed, getCurrentInstance, ref, watch } from 'vue';
import {
  Filter,
  GetPreregistrations,
  Grade,
  Period,
  PreRegistrationList,
  PreregistrationLoad,
  Processes,
  ReportOptions,
  School,
  StatProcesses,
} from '../types';
import { Nullable, Option, PaginatorInfo } from '@/types';
import {
  Preregistration as PreregistrationApi,
  PreregistrationRest,
} from '@/modules/preregistration/api';
import {
  preRegistrationStatusClass,
  preRegistrationStatusText,
  reportsTemplates,
  sortPreRegistrations,
} from '@/util';
import {
  useLoader,
  useLoaderAndShowErrorByModal,
  useModal,
} from '@/composables';
import { useRoute, useRouter } from 'vue-router';
import ModalComponent from '@/components/elements/Modal.vue';
import MultiSelectCustom from '@/components/form/MultiSelectCustom.vue';
import Pagination from '@/components/resource/Pagination.vue';
import PreRegistrationBatch from '@/modules/preregistration/components/PreRegistrationBatch.vue';
import SimpleSelect from '@/components/form/SimpleSelect.vue';
import SkeletonTable from '@/components/loaders/components/Table.vue';
import StatsCards from '@/components/elements/StatsCards.vue';
import XBtn from '@/components/elements/buttons/XBtn.vue';
import XCard from '@/components/elements/cards/XCard.vue';
import XCardSection from '@/components/elements/cards/XCardSection.vue';
import XField from '@/components/x-form/XField.vue';
import fileDownload from 'js-file-download';
import { useGeneralStore } from '@/store/general';

const { dialog } = useModal();

const { loader } = useLoader<PreregistrationLoad>();

const { loader: loaderPreregistrations } = useLoader<GetPreregistrations>();
const { loader: loaderNextInLine, loading: loadingNextInLine } = useLoader<{
  nextInLine: Nullable<{ id: string; protocol: string }>;
}>();
const { loader: loaderToExport, loading } = useLoader<Blob>();

const appContext = getCurrentInstance()?.appContext;

const { loader: loaderToReport, loading: loadingReport } =
  useLoaderAndShowErrorByModal<Blob>(
    appContext as AppContext,
    ref({
      title: 'Atenção!',
      description:
        'Não foi possível emitir o relatório. Verifique os filtros e tente novamente.',
    })
  );

const store = useGeneralStore();
const router = useRouter();
const route = useRoute();
const reactiveRoute = computed(() => route);
const alreadyLoaded = ref(false);

const fieldsModal = ref();

const processYear = ref<Nullable<number>>(null);
const showBatchModal = ref(false);
const step = ref<string>();
const allChecked = ref(false);
const checked = ref<string[]>([]);
const stats = ref<StatProcesses>();
const paginator = ref<PaginatorInfo>();
const preregistrations = ref<PreRegistrationList[]>([]);
const newFilter = ref<Filter>({
  search: '',
  process: null,
  school: null,
  grade: null,
  grades: [],
  canAcceptInBatch: false,
  sort: null,
  year: null,
  schools: null,
  processes: [],
});
const filter = ref<Filter>({
  search: '',
  process: null,
  school: null,
  grade: null,
  grades: [],
  sort: null,
  canAcceptInBatch: false,
  year: null,
  schools: null,
  processes: [],
});
const processes = ref<Processes[]>([]);
const schools = ref<School[]>([]);
const grades = ref<Grade[]>([]);
const periods = ref<Period[]>([]);
const types = ref(['REGISTRATION', 'REGISTRATION_RENEWAL', 'WAITING_LIST']);
const statuses = ref([
  'REJECTED',
  'ACCEPTED',
  'WAITING',
  'SUMMONED',
  'IN_CONFIRMATION',
]);
const years = ref<Option[]>([]);
const limitTableRows = ref(25);
const limitTableRowsOptions = ref<Option[]>([
  { key: 25, label: '25' },
  { key: 50, label: '50' },
  { key: 100, label: '100' },
  { key: 200, label: '200' },
  { key: 400, label: '400' },
]);
const loadingTable = ref(true);
const showModalReportOptions = ref(false);
const showModalSiecImport = ref(false);
const loadingSiecImport = ref(false);
const siecImport = ref<{
  processId: Nullable<string>;
  schoolId: Nullable<string>;
  gradeId: Nullable<string>;
  file: File | null;
}>({
  processId: null,
  schoolId: null,
  gradeId: null,
  file: null,
});

const reportOptions = ref<ReportOptions>({
  template: 1,
  showStudentShortName: true,
  disregardStudentsIeducar: false,
});

const siecProcessOptions = computed(() => {
  return processes.value.map((process) => ({
    key: process.id ?? process.key,
    label: process.name ?? process.label,
  }));
});

const siecSchoolOptions = computed(() => {
  return schools.value.map((school) => ({
    key: school.id ?? school.key,
    label: school.name ?? school.label,
  }));
});

const siecGradeOptions = computed(() => {
  return grades.value.map((grade) => ({
    key: grade.id ?? grade.key,
    label: grade.name ?? grade.label,
  }));
});

const rejectDisabled = computed(() => {
  return checked.value.length === 0;
});

const acceptDisabled = computed(() => {
  if (!filter.value.canAcceptInBatch) {
    return true;
  }
  return checked.value.length === 0;
});

const summonDisabled = computed(() => {
  return checked.value.length === 0;
});

const filteredProcesses = computed(() => {
  let filteredProcesses: Processes[] = [];
  if (filter.value.year) {
    filteredProcesses = processes.value.filter(
      (process) => process.schoolYear.year === filter.value.year
    );
  }
  return filteredProcesses.map((e) => ({ value: e.key, ...e }));
});

const filteredGrades = computed(() => {
  return grades.value.map((e) => ({ value: e.key, ...e }));
});

const getTypes = computed(() => {
  const type = {
    key: null,
    label: 'Nenhum',
  };

  const mappedTypes: Option[] = types.value.map((t) => ({
    key: t,
    label: getType(t),
  }));

  mappedTypes.unshift(type);

  return mappedTypes;
});

const getPeriods = computed(() => {
  const type = {
    key: null,
    label: 'Nenhum',
  };

  const types: Option[] = periods.value.map((period) => ({
    key: period.id,
    label: period.name,
  }));

  types.unshift(type);

  return types;
});

const getStatuses = computed(() => {
  const type = {
    key: null,
    label: 'Nenhum',
  };

  const types: Option[] = statuses.value.map((status) => ({
    key: status,
    label: preRegistrationStatusText(status),
  }));

  types.unshift(type);

  return types;
});

const noData = computed(() => {
  return loadingTable.value === false && preregistrations.value.length === 0;
});

const monthLabels = [
  'Janeiro',
  'Fevereiro',
  'Março',
  'Abril',
  'Maio',
  'Junho',
  'Julho',
  'Agosto',
  'Setembro',
  'Outubro',
  'Novembro',
  'Dezembro',
];

const formatMonthGroup = (dateValue?: string) => {
  const key = monthKey(dateValue);

  if (key === 'unknown') {
    return 'Sem data';
  }

  const [year, month] = key.split('-');

  return `${monthLabels[Number(month) - 1]} ${year}`;
};

const monthKey = (dateValue?: string) => {
  if (!dateValue) {
    return 'unknown';
  }

  const match = String(dateValue).match(/^(\d{4})-(\d{2})/);

  if (match) {
    return `${match[1]}-${match[2]}`;
  }

  const date = new Date(dateValue);

  if (Number.isNaN(date.getTime())) {
    return 'unknown';
  }

  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
};

type PreregistrationRow =
  | { type: 'month'; key: string; label: string }
  | { type: 'item'; key: string; item: PreRegistrationList };

const preregistrationRows = computed((): PreregistrationRow[] => {
  const rows: PreregistrationRow[] = [];
  let lastMonth: string | null = null;
  const groupByMonth = !filter.value.sort;

  preregistrations.value.forEach((item) => {
    if (groupByMonth) {
      const currentMonth = monthKey(item.date);

      if (currentMonth !== lastMonth) {
        rows.push({
          type: 'month',
          key: `month-${currentMonth}`,
          label: formatMonthGroup(item.date),
        });
        lastMonth = currentMonth;
      }
    }

    rows.push({
      type: 'item',
      key: `item-${item.id}`,
      item,
    });
  });

  return rows;
});

const toExport = () => {
  const copyFilter = { ...filter.value };

  if (copyFilter.processes?.length === 1) {
    copyFilter.process = copyFilter.processes[0];
  }

  loaderToExport(() => PreregistrationRest.toExport(copyFilter)).then((res) => {
    fileDownload(res, 'pre-matricula.csv');
  });
};

const accept = () => {
  const filteredProcessYear = processes.value.find(
    (process) => process.id === filter.value.process
  ) as Processes;
  processYear.value = filteredProcessYear.schoolYear.year;
  step.value = 'ACCEPT';
  showBatchModal.value = true;
};

const reject = () => {
  step.value = 'REJECT';
  showBatchModal.value = true;
};

const summon = () => {
  step.value = 'SUMMON';
  showBatchModal.value = true;
};

const load = (page?: number) => {
  loadingTable.value = true;
  alreadyLoaded.value = true;

  const copyFilter = { ...filter.value, page };

  if (copyFilter.processes?.length === 1) {
    copyFilter.process = copyFilter.processes[0];
  }

  if (copyFilter.grades?.length === 1) {
    copyFilter.grade = copyFilter.grades[0];
  }

  Object.keys(copyFilter).forEach((key) => {
    if (
      !copyFilter[key as keyof typeof copyFilter] ||
      copyFilter[key as keyof typeof copyFilter] === ''
    ) {
      delete copyFilter[key as keyof typeof copyFilter];
    }
  });

  if (copyFilter.processes?.length === 0) {
    delete copyFilter['processes' as keyof typeof copyFilter];
  }

  if (copyFilter.grades?.length === 0) {
    delete copyFilter['grades' as keyof typeof copyFilter];
  }

  if (store.auth.user && store.auth.user.level >= 4) {
    copyFilter.schools = store.auth.user.schools;
  }

  /**
   * Realiza checagem destes campos abaixo, pois os mesmos devem
   * ser números.
   *
   * Este problema deve acontecer em alguns navegadores que não disponibiliza a
   * opção de desativar o autocomplete, como é feito na grande maioria dos browsers.
   *
   * Exemplo
   *
   * Permitido:
   *
   * grade: "5"
   *
   * Não Permitido:
   *
   * grade: "BI"
   */
  const checkNumberType = ['period', 'process', 'school', 'grade'];

  checkNumberType.forEach((key) => {
    let item = copyFilter[key as keyof typeof copyFilter];

    if (!Number(item)) {
      delete copyFilter[key as keyof typeof copyFilter];
    }
  });

  loader(() =>
    PreregistrationApi.getList({
      first: Number(limitTableRows.value),
      ...copyFilter,
    })
  )
    .then((res) => {
      if (!res) return;

      stats.value = res.stats;
      preregistrations.value = res.preregistrations;

      paginator.value = res.paginator;
      checked.value = [];
      const { process, school, grade } = filter.value;
      if (process && school && grade) {
        filter.value.canAcceptInBatch = true;
      }
    })
    .finally(() => {
      loadingTable.value = false;
    });
};

const checkOne = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.checked) {
    allChecked.value = false;
  }
  if (checked.value.length === preregistrations.value.length) {
    allChecked.value = true;
  }
};

const checkAll = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.checked) {
    checked.value = preregistrations.value.map((i) => i.id);
  } else {
    checked.value = [];
  }
};

const cleanFilter = () => {
  filter.value = { ...newFilter.value };
  load(1);
};

const toReport = () => {
  const filters = { ...filter.value };
  Object.assign(filters, { ...reportOptions.value });

  loaderToReport(() => PreregistrationRest.toReport(filters))
    .then((res) => {
      fileDownload(res, 'pre-matricula.pdf');
    })
    .finally(() => {
      showModalReportOptions.value = false;
    });
};

const openSiecImportModal = () => {
  siecImport.value.file = null;
  siecImport.value.processId =
    filter.value.processes?.length === 1
      ? (filter.value.processes[0] as string)
      : null;
  siecImport.value.schoolId = (filter.value.school as string) || null;
  siecImport.value.gradeId =
    filter.value.grades?.length === 1
      ? (filter.value.grades[0] as string)
      : ((filter.value.grade as string) || null);
  showModalSiecImport.value = true;
};

const onSiecFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  siecImport.value.file = target.files?.[0] ?? null;
};

const importSiec = async () => {
  if (
    !siecImport.value.processId ||
    !siecImport.value.schoolId ||
    !siecImport.value.gradeId ||
    !siecImport.value.file
  ) {
    return;
  }

  loadingSiecImport.value = true;

  try {
    const res = await PreregistrationRest.toSiecImport(
      siecImport.value.file,
      siecImport.value.processId,
      siecImport.value.schoolId,
      siecImport.value.gradeId
    );

    showModalSiecImport.value = false;
    dialog({
      title: 'Sucesso!',
      description: `${res.imported} novas inscrições importadas com sucesso do SIEC!`,
      titleClass: 'success',
      iconLeft: 'status-green',
    });
    load(1);
  } catch (error: unknown) {
    const message =
      (error as { response?: { data?: { message?: string } } })?.response?.data
        ?.message || 'Não foi possível importar o arquivo SIEC.';
    dialog({
      title: 'Atenção!',
      description: message,
      titleClass: 'danger',
      iconLeft: 'status-red',
    });
  } finally {
    loadingSiecImport.value = false;
  }
};

const getType = (type: string) => {
  switch (type) {
    case 'REGISTRATION':
      return 'Matrícula';
    case 'REGISTRATION_RENEWAL':
      return 'Rematrícula';
    case 'WAITING_LIST':
    default:
      return 'Lista de espera';
  }
};

const badgeType = (type: string) => {
  switch (type) {
    case 'REGISTRATION':
      return 'badge-blue';
    case 'REGISTRATION_RENEWAL':
      return 'badge-cyan';
    case 'WAITING_LIST':
    default:
      return 'badge-yellow';
  }
};

const showPreRegistration = (protocol: string) => {
  router.push({
    name: 'preregistration.modal',
    params: {
      protocol,
    },
  });
};

const summonNextInLine = () => {
  loaderNextInLine(() =>
    PreregistrationApi.getNextInLine({
      process: filter.value.processes[0] as string,
      school: filter.value.school as string,
      grade: filter.value.grades[0] as string,
      period: filter.value.period as string,
    })
  ).then((res) => {
    if (res.nextInLine) {
      showPreRegistration(res.nextInLine.protocol);
    } else {
      dialog({
        title: 'Atenção!',
        description: 'Não há nenhuma inscrição em espera.',
        titleClass: 'danger',
        iconLeft: 'status-red',
      });
      return;
    }
  });
};

const getData = () => {
  const variables: {
    schools?: string[];
  } = {};
  if (store.auth.user && store.auth.user.level >= 4) {
    variables.schools = store.auth.user.schools;
  }

  loaderPreregistrations(() =>
    PreregistrationApi.getPreregistrations(variables)
  ).then((res) => {
    processes.value = res.processes.sortBy('name');
    schools.value = res.schools.sortBy('name');
    grades.value = res.grades.sortBy('name');
    periods.value = res.periods.sortBy('name');
    const processYearWhereExistsPreRegistration: number[] = [];
    const processYearWhereNotExistsPreRegistration: number[] = [];
    res.processes.forEach((process) => {
      if (process.totalPreRegistrations > 0) {
        processYearWhereExistsPreRegistration.push(process.schoolYear.year);
      } else {
        processYearWhereNotExistsPreRegistration.push(process.schoolYear.year);
      }
    });
    processYearWhereExistsPreRegistration.sort().reverse();
    const yearMostRecent = processYearWhereExistsPreRegistration[0];
    years.value = processYearWhereExistsPreRegistration
      .concat(processYearWhereNotExistsPreRegistration)
      .filter((v, i, a) => a.indexOf(v) === i)
      .map((year) => ({ key: year, label: year }))
      .sortBy('key')
      .reverse() as unknown as Option[];
    if (yearMostRecent) {
      filter.value.year = yearMostRecent;
    }
    if (schools.value.length === 1) {
      filter.value.school = schools.value[0].id;
    }
    load(1);
  });
};

getData();

watch(
  reactiveRoute,
  (val) => {
    // Evita que toda vez que modal seja exibida faça uma requisição de listagem
    if (val.name === 'preregistration.modal') {
      return;
    }

    load((val.query.page as unknown as number) || 1);
  },
  {
    deep: true,
  }
);

watch(
  limitTableRows,
  () => {
    if (paginator.value) {
      load(paginator.value.currentPage);
    }
  },
  {
    deep: true,
  }
);

const ctx = {
  toExport,
};
</script>

<style scoped>
.table-month-group td {
  background-color: #e9ecef;
  border-top: 2px solid #ced4da;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
}
</style>
