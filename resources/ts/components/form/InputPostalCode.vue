<template>
  <div class="input-group">
    <input
      v-mask="mask"
      v-bind="$attrs"
      :autocomplete="unique"
      class="form-control"
      type="tel"
      @keydown.enter.prevent.stop="search"
    />
    <div class="input-group-append">
      <x-btn
        label="Buscar"
        color="primary"
        class="flex-row"
        style="border-top-left-radius: 0px; border-bottom-left-radius: 0px"
        no-caps
        no-wrap
        :loading="loading"
        loading-normal
        @click="search"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { PropType, computed, defineComponent, onMounted, ref } from 'vue';
import { Nullable } from '@/types';
import XBtn from '@/components/elements/buttons/XBtn.vue';
import axios from 'axios';
import { mask } from 'vue-the-mask';
import { useGeneralStore } from '@/store/general';
export default defineComponent({
  components: {
    XBtn,
  },
  directives: {
    mask,
  },
  props: {
    unique: {
      type: String as PropType<string>,
      default: null,
    },
  },
  emits: ['change:address', 'notFound'],
  setup(props, { attrs, emit }) {
    const store = useGeneralStore();

    const geocoder = ref<google.maps.Geocoder>();
    const mask = ref('#####-###');
    const loading = ref(false);

    const onlyNumbers = computed(() =>
      attrs.cep ? (attrs.cep as string).replace(/[^[0-9]]*/, '') : ''
    );
    const disabled = computed(() => onlyNumbers.value.length < 8);

    const search = async () => {
      loading.value = true;
      try {
        try {
          const viaCepRes = await axios.get(
          `https://viacep.com.br/ws/${onlyNumbers.value}/json/`
        );
        if (!viaCepRes.data.erro) {
          emit('change:address', viaCepRes.data);
          return;
        }
      } catch {
      }

      try {
        const openCepRes = await axios.get(
          `https://opencep.com/v1/${onlyNumbers.value}`
        );
        const data = openCepRes.data;
        if (data?.localidade) {
          emit('change:address', data);
          return;
        }
      } catch {
      }

      try {
        const awesomeRes = await axios.get(
          `https://cep.awesomeapi.com.br/json/${onlyNumbers.value}`
        );
        const data = awesomeRes.data;
        if (data?.city) {
          emit('change:address', {
            cep: data.cep,
            logradouro: data.address ?? '',
            complemento: '',
            bairro: data.district ?? '',
            localidade: data.city,
            uf: data.state,
            ibge: data.city_ibge ?? '',
          });
          return;
        }
      } catch {
      }

      searchFromGoogle();
    } finally {
      loading.value = false;
    }
    };

    const searchFromGoogle = () => {
      if (!geocoder.value) return;

      const address = /* ${model.value},  */ `${store.entity.city}, ${store.entity.state}`;

      geocoder.value.geocode(
        { address },
        (
          results: Nullable<google.maps.GeocoderResult[]>,
          status: google.maps.GeocoderStatus
        ) => {
          if (status === 'OK' && results) {
            const { formatted_address: result } = results[0];
            const city = result.includes(store.entity.city);
            const state = result.includes(store.entity.state);
            if (city && state) {
              emit('change:address', {
                logradouro: null,
                complemento: null,
                bairro: null,
                localidade: store.entity.city,
                uf: store.entity.state,
                ibge: null,
              });
              return;
            }
          }
          emit('notFound');
        }
      );
    };

    onMounted(() => {
      geocoder.value = new google.maps.Geocoder();
    });

    return {
      mask,
      loading,
      search,
      onlyNumbers,
      disabled,
    };
  },
});
</script>
