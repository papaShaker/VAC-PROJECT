<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onBeforeMount, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ModalInfoHolidays from '@/Components/ModalInfoHolidays.vue';
import { useUserStore } from '@/stores/userStore';

const props = defineProps({
    departments: Array,
});
const userStore = useUserStore();

const jobRanges = ref([]);
const calendar_data = reactive({});
const admin_toggled = ref(false);
const users = ref([]);
const selected_department_id = ref('');
const selected_department_name = ref('');
const selected_month = ref('');
const selected_month_name = ref('');
const user = ref(null);
const year = ref(null);
const is_admin = ref(null);
const vacations_index = reactive({});
const vacations_calendar = reactive({});
const currentDate = new Date();
const loading = ref(true);
const spinner = ref(false);
const months = reactive([]);
const week_days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb', 'Dom'];
const holidays_data = reactive([]);
const selected_month_check = ref(false);

/* HOLIDAYS TO BE CONFIRMED LIST */
const holidays_to_be_confirmed_list = reactive([]);
const loaded_to_be_confirmed_data = ref(false);
const vac_states = reactive([]);
const ranges_checkbox_list = reactive([]);
const show_modal_holidays_to_be_confirmed = ref(false);
const show_modal_job_range = ref(false);
const selected_moda_job_range = ref(false);
const selected_modal_holidays_to_be_confirmed = ref(false);
const modal_holidays_to_be_confirmed = reactive({
    title: '',
    id: 0,
    loading: false,
    holidays: [],
});
const modal_job_range = reactive({
    title: '',
    current_ranges: [],
    paid_holidays: [],
    contract_types: [],
    paid_holidays_days: {
        days: 0,
        comment: '',
        date: null
    },
    new_start_date: null,
    new_end_date: null,
    new_contract_type: null,
    new_work_on_freedays: false,
    form_add_visible: false,
    form_add_paid_holidays: false
});
const selected_modal_job_range = ref(false);

const toast_options = {
    closeOnClick: true,
    toastStyle: {
        marginRight: '5px',
        backgroundColor: '#374151',
        color: '#DFECEC',
        borderRadius: '4px',
        border: '1px solid #9CA3AF'
    }
}

/* DEPARTMENT SELECTOR IF ROLE */
const ifNoPermissionDepartment = async () => {
    if (!userStore.hasPermission('view department selector') || !userStore.hasRole('rrhh')) {
        selected_department_id.value = userStore.user.department;
        await fetchDepartmentNameById(selected_department_id.value);
        console.log(selected_department_id);
    }
};

/* HOLIDAYS TO BE CONFIRMED LIST END */

const fetchJobRanges = () => {
    axios.get(`api/allJobRanges`).then((response) => {
        console.log("FETCH JOBS");
        console.log(response.data);
        jobRanges.value = response.data;
    }).catch(error => {
        console.error('Error', error);
    })
};

const fetchUsers = (department_id) => {
    axios.get(`/departments/${department_id}/users`).then((response) => {
        users.value = response.data;
    }).catch(error => {
        console.error('Error', error);
    })
};

const fetchRolesAndPermissionsForUser = () => {
    axios.get(`/roles/permissions`).then((response) => {
        console.log(response.data);
    }).catch(error => {
        console.error('Error', error);
    })
};

const fetchDepartmentNameById = async (department_id) => {
    while (holidays_data.length > 0) {
        holidays_data.pop();
    }
    Object.keys(calendar_data).forEach(key => delete calendar_data[key]);
    selected_month.value = '';
    selected_month_name.value = '';
    console.log("entra");
    try {
        await axios.get('/api/department_name/' + department_id)
            .then((response) => {
                selected_department_name.value = response.data;
                console.log(selected_department_name.value);
            });
    } catch (error) {

    }
}

const getUserById = async (user_id) => {
    try {
        console.log(user_id);
        const response = await axios.get('/api/user/' + user_id);
        return response.data;
        // Now you can safely access the user data here
    } catch (error) {
        console.error('Error fetching user:', error);
    }
};

const fetchCalendarData = async (consulted_year = null, consulted_department_id, consulted_month) => {
    loading.value = true;
    spinner.value = false;
    selected_month_check.value = false;
    year.value = consulted_year === null ? new Date().getFullYear() : consulted_year
    let url = (consulted_department_id === null && consulted_month == null ? 'api/calendar_department/' + year.value : (consulted_month) ? 'api/calendar_department/' + year.value + '/' + consulted_department_id + '/' + consulted_month : 'api/calendar_department/' + year.value + '/' + consulted_department_id);
    consulted_year = year.value;
    await axios.get(url)
        .then((response) => {
            loading.value = false;
            spinner.value = true;
            // Empty the existing content of calendar_data
            Object.keys(calendar_data).forEach(key => delete calendar_data[key]);
            Object.assign(calendar_data, response.data);
            console.log("CALENDAR_DATA");
            console.log(calendar_data);
            getHolidaysRelevantInfo(response.data);

            // Set selected_month_check based on the presence of is_holiday == 1
            selected_month_check.value = true;
        })
        .catch((error) => { console.error(error) });
}

const getHolidaysRelevantInfo = (passed_data) => {
    console.log("Entra a Relevant");
    while (holidays_data.length > 0) {
        holidays_data.pop();
    }

    passed_data.forEach(index => {
        let now = new Date().toISOString().slice(0, 10);
        let data = [{ days: {} }];
        let days = index.days;
        console.log(now);
        for (let date in days) {
            let dayEntry = days[date];
            if (dayEntry.is_holiday > 0) {
                dayEntry.holidays.forEach(holiday => {
                    if (date <= now) {
                        dayEntry.is_holiday = 3; // Updates is_holiday at 3 for Disfrutadas.
                    }
                    if (date > now && holiday.user_holiday_status === 2) {
                        dayEntry.is_holiday = 2; // Updates is_holiday at 2 for Confirmadas.
                    }
                    holidays_data.push({
                        date: date,
                        user_name: holiday.user_name,
                        user_holiday_status: (date <= now) ? "Disfrutadas" : (holiday.user_holiday_status === 2) ? "Confirmadas" : "Pendientes"
                    });
                });
            }

        }
        holidays_data.reverse();
    });
    console.log("HOLIDAYS_DATA");
    console.log(holidays_data.length);
};

const generateMonthOptions = async () => {
    let now = new Date();
    let currentYear = now.getFullYear();
    let monthNames = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    for (let i = 0; i < 12; i++) {
        months.push({
            label: `${monthNames[i]} ${currentYear}`,
            value: `${currentYear}-${String(i + 1).padStart(2, '0')}`
        });
    }
    months.push({
        label: `Enero ${currentYear + 1}`,
        value: `${currentYear + 1}-01`
    });

    months.push({
        label: `Todos los meses`,
        value: ``
    });
};

const selected_month_label = () => {
    let selectedMonth = months.find(month => month.value === selected_month.value);
    if (selectedMonth) {
        selected_month_name.value = selectedMonth.label; // Set the label if found
        console.log("Selected month label:", selected_month_name.value);
    } else {
        selected_month_name.value = "null"; // Set to "null" if not found
        console.log("No month found for value:", selected_month.value);
    }
};

const capitalize = (value) => {
    if (!value) return '';
    return value.charAt(0).toUpperCase() + value.slice(1);
};

const getTooltipText = (dayData) => {
    if (dayData.holidays && dayData.holidays.length > 0) {
        return dayData.holidays.map(holiday => {
            return `***${holiday.user_name}`;
        }).join(',\n'); // Use <br> for line breaks in the tooltip
    } else {
        return "";
    }
};


onBeforeMount(async () => {
    admin_toggled.value = false;
    await generateMonthOptions();
})

onMounted(() => {
    ifNoPermissionDepartment();
});

/* VACATION MANAGER FUNCTIONS */

const getHolidaysToBeConfirmed = async () => {
    await axios.get("api/vacations_to_be_confirmed")
        .then((response => {
            while(holidays_to_be_confirmed_list.length > 0) {
                holidays_to_be_confirmed_list.pop();
            }
            holidays_to_be_confirmed_list.push(...response.data);
            console.log(holidays_to_be_confirmed_list);
            loaded_to_be_confirmed_data.value = true;
        }
        ));
}

const manageHolidaysToBeConfirmed = (item) => {
    modal_holidays_to_be_confirmed.holidays = item.holidays_state.holidays_to_be_confirmed
    console.log(modal_holidays_to_be_confirmed.holidays);
    modal_holidays_to_be_confirmed.title = item.user_info.name;
    show_modal_holidays_to_be_confirmed.value = true;
}

const updateHolidays = (state) => {
    modal_holidays_to_be_confirmed.loading = true;
    let ids_to_be_updated = [];
    modal_holidays_to_be_confirmed.holidays.forEach(element => {
        if (element.selected) {
            ids_to_be_updated.push(element.id);
        }
        else {
            modal_holidays_to_be_confirmed.loading = false;
        }
    });
    if (ids_to_be_updated.length > 0) {
        axios.post('/manager/update/holidays', {ids_to_be_updated: ids_to_be_updated, update_to: state})
            .then((response) => {
                if(response.data.status == 'Success'){
                    toast.success(`${response.data.text}`, toast_options);
                    if (response.data.list != undefined) {
                        items = response.data.list;
                        modal_holidays_to_be_confirmed.holidays = [];
                        
                    }
                    
                } else {
                    toast.error(`${response.data.text}`, toast_options);
                    
                
                }
                modal_holidays_to_be_confirmed.loading = false;
            }).finally(() => {
                modal_holidays_to_be_confirmed.loading = false;
                closeModalInfoHolidays();
            });
    }
}

const closeModalInfoHolidays = () => {
    console.log("MODAL ON CLOSE");
    show_modal_holidays_to_be_confirmed.value = false;
    show_modal_job_range.value = false;
};

const manageRanges = (user) => {
    modal_job_range.current_ranges = [];
    axios.get('/manager/get_ranges/' + user.id).then((response) => {
        modal_job_range.current_ranges = response.data.ranges;
        modal_job_range.paid_holidays = response.data.paid_holidays;
        modal_job_range.contract_types = response.data.contract_types;
    });
    modal_job_range.title = user.name;
    modal_job_range.user_id = user.id;
    modal_job_range.form_add_visible = false;
    show_modal_job_range.value = true;
};

const handleCheckboxChangeJobRange = (contract) => {
    if (contract.selected) {
        ranges_checkbox_list.push(contract);
        console.log(ranges_checkbox_list);
    } else if (!contract.selected) {
        ranges_checkbox_list.pop();
    }

    if(ranges_checkbox_list.length > 0) {
            selected_modal_job_range.value = true;
    } else {
        selected_modal_job_range.value = false;
    }
};

const handleCheckboxChangeHolidaysToBeConfirmed = (vac) => {
    if (vac.selected) {
        vac_states.push(vac);
        console.log(vac_states);
    } else if (!vac.selected) {
        vac_states.pop();
    }

    if(vac_states.length > 0) {
            selected_modal_holidays_to_be_confirmed.value = true;
    } else {
        selected_modal_holidays_to_be_confirmed.value = false;
    }
};

/* VACATION MANAGER FUNCTIONS END */

</script>

<template>

    <Head title="Departamentos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight"
                style="font-family: 'Abel', sans-serif;">Bienvenido a Departamentos
            </h2>
        </template>

<!-- VACATION MANAGEMENT -->
<div class="pt-3" :class="[admin_toggled ? 'pt-3' : '']">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg" :class="[(admin_toggled) ? 'admin_toggled' : '',]">
                    <div v-if="userStore.hasPermission('view pending vacations table') || userStore.hasRole('department_boss')" class="py-2 flex justify-between items-center space-x-5 text-gray-900 dark:text-gray-100 bg-gray-800 px-4">
                        <div class="text-xl " style="font-family: 'Abel', sans-serif;"> PANEL DE GESTIÓN</div>
                        <div class="flex items-center">
                        <!-- Toggle Switch -->
                        <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" :checked="admin_toggled" @change="admin_toggled = !admin_toggled, getHolidaysToBeConfirmed()">
                                <div
                                class="w-12 h-6 bg-gray-300 rounded-full border-2 ring-white peer-checked:bg-gray-700 
                                        peer-focus:ring-1 peer-focus:ring-blue-100 transition-all">
                                </div>
                                <div
                                class="absolute text-gray-700 bg-white left-1 top-1 w-4 h-4 rounded-full shadow 
                                        peer-checked:translate-x-5 transition-transform peer-checked:text-green-500 peer-checked:left-1.5">
                                        <i class="fa-solid fa-wrench text-xs absolute top-0.6 left-0.5"></i>
                                    </div>
                            </label>
                        </div>
                    </div>
                    <div v-if="admin_toggled" class="p-2 pb-8 grid justify-center items-center space-x-5 text-gray-900 dark:text-gray-100">
                        <div class="sm:flex sm:justify-between items-center grid">
                        
    <!-- START TO BE CONFIRMED VACATIONS -->

                    <div class="relative overflow-x-auto my-5"
                        v-if="loaded_to_be_confirmed_data">
                        <table class="border-collapse border border-gray-100 text-sm text-left rtl:text-right text-gray-100 dark:text-gray-400">
                            <thead class="sm:w-[32rem] w-[24rem] text-xs border border-gray-100 text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="border border-gray-100">Usuario</th>
                                    <th class="border border-gray-100">Contrato</th>
                                    <th class="border border-gray-100">Zona</th>
                                    <th class="border border-gray-100">Vacaciones</th>
                                    <th class="border border-gray-100">Acumuladas</th>
                                    <th class="border border-gray-100">Extras</th>
                                    <th class="border border-gray-100">Confirmadas</th>
                                    <th class="border border-gray-100">Disfrutadas</th>
                                    <th class="border border-gray-100">Pagadas</th>
                                    <th class="border border-gray-100">Pe. Disfrutar</th>
                                    <th class="border border-gray-100">Pe. Confirmar</th>
                                    <th class="border border-gray-100">Pe. Solicitar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border border-gray-200 bg-gray-700" v-for="(item, index) in holidays_to_be_confirmed_list" :key="index">
                                    <td class="border border-gray-500">
                                        <span @click="item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? manageHolidaysToBeConfirmed(item) : false" class="tooltip-mvc">
                                            <a
                                                class="linkUsr text-gray-400">
                                                {{ item.user_info.name }}
                                            </a>
                                        </span>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="to_be_confirmed flex justify-center">
                                            <a class="jobRangeIcon" @click="manageRanges(item.user_info)"> <!--  -->
                                                <i class="fas fa-file-contract fa-2x text-[#39a33c]"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="to_be_confirmed tooltip-mvc">
                                            <a class="jobRangeIcon"
                                               > <!-- @click="gestionarZonaFestius(item.user_info)" -->
                                                <span v-if="item.user_info.nonworkingdayzone">{{ item.user_info.nonworkingdayzone.zone
                                                    }}</span>
                                                <span class="red" v-else>No configurada</span>
                                            </a> <!-- v-if="comanderVacances" -->
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.hours_per_contract.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ (item.holidays_state.hours_per_contract /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span class="numeric">{{ item.holidays_state.accumulated_holidays_days }}</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.hours_per_extra_days.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.extra_holidays_days }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span class="numeric">
                                                {{ (item.holidays_state.confirmed_holiday_hours /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.enjoyed_holiday_hours.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.enjoyed_holidays_days }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span class="numeric">{{ item.holidays_state.paid_holidays_days }}</span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span class="numeric">
                                                {{ (item.holidays_state.pending_holiday_hours_to_be_enjoyed /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span class="numeric to_be_confirmed tooltip-mvc"
                                                @click="item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? manageHolidaysToBeConfirmed(item) : false"
                                                :class="[item.holidays_state.pending_holiday_hours_to_be_confirmed > 0 ? 'pendientes' : '']">
                                                {{ (item.holidays_state.pending_holiday_hours_to_be_confirmed /
                                                    item.holidays_state.current_working_hours).toFixed(0) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="border border-gray-600">
                                        <div class="numeric_Container">
                                            <span data-bs-toggle="tooltip" data-placement="bottom"
                                                :title="item.holidays_state.holiday_hours_to_be_requested.toFixed(2) + ' horas.'"
                                                class="numeric">
                                                {{ item.holidays_state.pending_holidays_total_to_be_requested }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- MODAL UPDATE VACATIONS START -->

                    <ModalInfoHolidays v-if="show_modal_holidays_to_be_confirmed" :isOpen="show_modal_holidays_to_be_confirmed" @modal-close="closeModalInfoHolidays" name="modal-info-holidays" role="document">
                        <template v-slot:modal-header>
                            <p class="text-xl"> <span class="font-bold text-gray-300">Vacaciones de</span> <span class="text-white">{{modal_holidays_to_be_confirmed.title}} </span> </p>
                        </template>
                        <template v-slot:modal-content>
                                <div class="flex justify-around items-center" v-for="(vac, key) in modal_holidays_to_be_confirmed.holidays" :key="key">
                                    <div><span class="font-bold text-gray-300">Fecha solicitada:</span> <span class="font-bold text-gray-100">{{ vac.date }}</span></div>
                                    <input type="checkbox" @change="handleCheckboxChangeHolidaysToBeConfirmed(vac)" v-model="vac.selected" :id="key" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                </div>
                                <div v-if="modal_holidays_to_be_confirmed.loading" class="flex justify-center items-center min-h-24 pos-relative">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-transparent dark:bg-transparent">
                                        <div role="status">
                                            <svg aria-hidden="true" class="h-10 w-10 animate-spin fill-red-600 text-gray-200 dark:text-gray-400" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                                            </svg>
                                            <span class="sr-only">Cancelando...</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-slot:modal-footer>
                                <div v-if="selected_modal_holidays_to_be_confirmed" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-green-600 hover:bg-green-800 text-white font-bold py-1 px-2 m-1 rounded">Confirmar
                                        vacaciones</button>
                                    <button @click="updateHolidays(0)"
                                        class="bg-red-600 hover:bg-red-800 text-white font-bold py-1 px-2 m-1 rounded">Denegar
                                        vacaciones</button>
                                </div>
                            </template>
                    </ModalInfoHolidays>

                    <ModalInfoHolidays v-if="show_modal_job_range" :isOpen="show_modal_job_range" @modal-close="closeModalInfoHolidays" name="modal-info-holidays" role="document">
                        <template v-slot:modal-header>
                            <p class="text-xl"> <span class="font-bold text-gray-300">Contratos de</span> <span class="text-white">{{modal_job_range.title}} </span> </p>
                        </template>
                        <template v-slot:modal-content>
                            <div class="flex justify-center items-center" v-for="(contract, key) in modal_job_range.current_ranges" :key="key">
                                <div class="grid justify-center items-center">
                                    <div><span class="font-bold text-gray-300">Tipo de contrato:</span> <span class="font-bold text-gray-100">{{ contract.contract_type.name }}</span></div>
                                    <div><span class="font-bold text-gray-300">Jornada horas/día:</span> <span class="font-bold text-gray-100">{{ contract.contract_type.working_hours }}</span></div>
                                    <div><span class="font-bold text-gray-300">Fecha inicio:</span> <span class="font-bold text-gray-100">{{ contract.start_date }}</span></div>
                                    <div><span class="font-bold text-gray-300">Fecha fin:</span> <span class="font-bold text-gray-100">{{ contract.end_date }}</span></div>
                                </div>
                                <div class="grid justify-center items-center">
                                    <input @change="handleCheckboxChangeHolidaysToBeConfirmed(contract)" v-model="contract.selected" :id="key" type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"/>
                                </div>
                            </div>
                                <div v-if="modal_holidays_to_be_confirmed.loading" class="flex justify-center items-center min-h-24 pos-relative">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-transparent dark:bg-transparent">
                                        <div role="status">
                                            <svg aria-hidden="true" class="h-10 w-10 animate-spin fill-red-600 text-gray-200 dark:text-gray-400" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill" />
                                            </svg>
                                            <span class="sr-only">Cancelando...</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-slot:modal-footer>
                                <div v-if="!show_modal_job_range" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-2 m-1 rounded">Añadir contrato</button>
                                </div>
                                <div v-if="show_modal_job_range" class="flex justify-between mt-5 mx-2">
                                    <button @click="updateHolidays(1)"
                                        class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-2 m-1 rounded">Eliminar contrato</button>
                                    <button @click="closeModalInfoHolidays()"
                                        class="bg-gray-700 hover:bg-gray-500 text-white font-bold py-1 px-2 m-1 rounded">Cancelar</button>
                                </div>
                            </template>
                    </ModalInfoHolidays>

                    <!-- MODAL UPDATE VACATIONS END -->

                    <!-- END TO BE CONFIRMED VACATIONS -->

                        
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- VACATION MANAGEMENT END -->

        <div class="pb-6 pt-3"> <!-- Container-fluid -->
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <h1 class="mt-12 text-center sm:text-2xl text-xl text-gray-700 font-semibol dark:text-white" style="font-family: 'Abel', sans-serif;">Resumen de vacaciones <span v-if="selected_department_name">- {{ selected_department_name }}</span> <span v-if="selected_month_name"> - {{ selected_month_name }}</span></h1>
                    <div class="p-2 sm:pt-12 pt-6 pb-8 grid items-center text-gray-900 dark:text-gray-100 sm:flex sm:justify-between sm:px-56 px-2">
                        <div  class="sm:flex sm:justify-center sm:ml-5 grid items-center space-x-5 items_spacing_y w-[350px]">
                            <h4 class="flex ml-5">Departamento:</h4>
                            <form class="flex max-w-sm mx-auto items-center" @submit.prevent="">
                                <label for="departments" class="text-xl font-medium text-gray-900 dark:text-white">
                                </label>
                                <select v-if="userStore.hasPermission('view department selector') || userStore.hasRole('rrhh')" id="departments" v-model="selected_department_id"
                                    @change="fetchDepartmentNameById(selected_department_id);"
                                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                    <option selected disabled value="">Selecciona un departamento</option>
                                    <option v-for="department in departments" :key="department.id" :value="department.id">
                                        {{ department.name }}
                                    </option>
                                </select>
                                <select v-else
                                    class="sm:max-w-[145px] max-w-[350px] sm:min-w-36 min-w-24 flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    id="departments" v-model="selected_department_id"
                                    @change="fetchDepartmentNameById(selected_department_id);">
                                    <option selected disabled :key="userStore.user.department" :value="userStore.user.department">
                                        {{ selected_department_name }}
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="sm:flex sm:justify-center sm:ml-5 grid items-center space-x-5 items_spacing_y w-[350px]">
                            <h4 class="flex ml-5">Mes:</h4>
                            <form class="flex max-w-sm mx-auto items-center">
                                <label for="month" class="text-xl font-medium text-gray-900 dark:text-white"></label>
                                <select
                                    class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    :disabled="!selected_department_id" v-model="selected_month"
                                    @change="fetchCalendarData(null, selected_department_id, selected_month); selected_month_label();">
                                    <option value="" disabled selected>Selecciona un mes</option>
                                    <!-- Placeholder option -->
                                    <option v-for="month in months" :key="month.value" :value="month.value">
                                        {{ month.label }}
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="p-4 flex items-center justify-center text-gray-900 dark:text-gray-100">
                        <!-- START BOX -->
                        <div v-if="!loading">
                            <!-- PY-5 -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="px-3 text-gray-900 dark:text-gray-100">
                                    <!-- Vacations header info (user-year) -->
                                    <p v-if="selected_month" class="mt-2 mb-4 text-xl text-gray-300"
                                        style="font-family: 'Abel', sans-serif;"> Vacaciones de <span
                                            class="text-white">{{
                                                selected_department_name }}</span> - <span class="text-white">
                                            {{ selected_month_name }} </span>
                                    </p>
                                </div>
                                <div class="flex justify-around mb-5 space-y-1 grid_style_legend">
                                    <!-- Legend container -->
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 today rounded-md"><!-- Today -->
                                        </div>
                                        <div>
                                            <p class="text-white">Hoy.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-yellow-100 rounded-md">
                                            <!-- Pending holidays -->
                                        </div>
                                        <div>
                                            <p class="text-white">Vacaciones pendientes.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-green-400 rounded-md">
                                            <!-- Confirmed holidays -->
                                        </div>
                                        <div>
                                            <p class="text-white">Vacaciones confirmadas.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 past_holiday rounded-md">
                                            <!-- Work on free days -->
                                        </div>
                                        <div>
                                            <p class="text-white">Vacaciones disfrutadas.</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-around mx-2 items-center"> <!-- Legend div -->
                                        <div class="w-6 h-6 mr-1.5 bg-red-400 rounded-md"><!-- Free days -->
                                        </div>
                                        <div>
                                            <p class="text-white">Día festivo / Fin de semana.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 flex justify-around flex-wrap text-white mb-4">
                                    <!-- START OF DIV -->
                                    <!-- calendarios - Calendar outer div -->
                                    <div class="relative w-72 box-border shadow-lg rounded-lg border border-solid border-gray-600 m-2"
                                        :class="[month.previous_month ? 'past_month' : '']"
                                        v-for="(month, key) in calendar_data" :key="key"> <!-- calendar -->
                                        <div class="w-full text-center text-[22px] m-0 text-gray-200 bg-gray-700">
                                            <!-- calendar__mes - Month name - Header -->
                                            {{ capitalize(month.month_name) }}
                                        </div>
                                        <div
                                            class="p-5 grid grid-cols-[repeat(auto-fit,minmax(25px,1fr))] gap-2.5 box-border">
                                            <!-- calendar__date - Month body -->
                                            <div v-for="day in week_days" :key="day"
                                                class="flex items-center justify-center h-[25px] font-semibold text-white">
                                                <!-- calendar__day - Week headings - v-for -->
                                                <div>{{ day }}</div>
                                            </div>
                                            <div @click="(!days.day.locked_already_requested_day && days.day.range_working_hours != 0) || user.value.admin ? selectDay(days, key, month) : false"
                                                v-for="(days, key) in month.days" :key="key"
                                                class="flex items-center justify-center h-6 text-gray-300 rounded-md cursor-pointer" :tooltip-mvc="getTooltipText(days)"
                                                :class="[getTooltipText(days)!='' ? 'tooltip-mvc' : '',
                                                month.prevous_month ? 'past_month_text' : '',
                                                (days.free_day && !days.work_on_freedays) ? 'free_day' : '',
                                                (days.free_day && days.work_on_freedays) ? 'work_on_freedays' : '',
                                                days.is_holiday == 1 ? 'pending_holiday' : '',
                                                days.is_holiday == 2 ? 'confirmed_holiday' : '',
                                                days.is_holiday == 3 ? 'past_holiday' : '',
                                                days.day_selected ? 'day_selected' : '',
                                                days.locked_already_requested_day && days.holiday == 0 ? 'locked_already_requested_day' : '',
                                                days.range_working_hours == 0 ? 'not_ranged_day' : '',
                                                days.holiday == 1 ? 'pending_holiday' : '',
                                                days.day_state == 1 ? 'today' : !month.previous_month && days.day_state == 0 ? 'passed_day' : '']">
                                                <!-- calendar__number - actual month dates-->
                                                <div class="">
                                                    {{ days.day }}
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- v-for - each Month div -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- START TABLE -->
                    <div class="pb-6 flex items-center justify-center text-gray-900 dark:text-gray-100"
                        v-if="selected_month_check">
                        <div v-if="holidays_data.length > 0" class="relative overflow-x-auto mt-5">
                            <h4 class="mb-4 text-xl text-gray-300" style="font-family: Abel, sans-serif;"> Lista de vacaciones - {{ selected_month_name }}</h4>
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-3 py-3">Fecha</th>
                                        <th scope="col" class="px-3 py-3">Usuario</th>
                                        <th scope="col" class="px-3 py-3">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700"
                                        v-for="holiday in holidays_data" :key="holiday">
                                        <td scope="row"
                                            class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.date }}
                                        </td>
                                        <td scope="row"
                                            class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{
                                                holiday.user_name }}
                                        </td>
                                        <td scope="row"
                                            class="px-3 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-gray-600 italic"
                                            :class="[(holiday.user_holiday_status == 'Confirmadas') ? 'bg-green-300' : (holiday.user_holiday_status == 'Disfrutadas') ? 'bg-orange-300' : 'bg-yellow-100']">
                                            {{ holiday.user_holiday_status }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else-if="!spinner && selected_department_name && selected_month" role="status"
                        class="flex items-center justify-center mb-12"> <!-- WHILE LOADING -->
                        <svg aria-hidden="true"
                            class="inline w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-green-500"
                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor" />
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill" />
                        </svg>
                        <span class="sr-only">Loading...</span>
                    </div>

                    <!-- END TABLE -->

                </div>
            </div>
            <!-- <div><button @click="fetchRolesAndPermissionsForUser()">
                    <p class="text-green-200">Send to check roles and permissions.</p>
                </button></div>
            <div><button @click="fetchUsers(selected_department_id)">
                    <p class="text-green-200">Send to check users.</p>
                </button></div>
            <div><button @click="fetchJobRanges">
                    <p class="text-green-200">Send to get All Job Ranges.</p>
                </button></div> -->
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.past_month {
    background-color: #25232c;
}

.past_month_text {
    color: #4e4e50;
    cursor: context-menu;
}

.free_day {
    background-color: #F87171 !important;
    color: #fff !important;
    cursor: context-menu;
}

.work_on_freedays {
    border: 1px solid #F87171;
    color: #ffffff !important;
}

.confirmed_holiday {
    background-color: #4ADE80;
    color: #4e4e50 !important;
}

.past_holiday {
    background-color: #FDBA74;
    color: #3d3d3d !important;
}

.pending_holiday {
    background-color: #FEF9C3;
    color: #3d3d3d !important;
}

.day_selected {
    background-color: #57ddff;
    color: #4e4e50 !important;
}

.locked_already_requested_day {
    cursor: context-menu;
    color: #4e4e50 !important;
    text-shadow: 0px 0px 2px #ffffff;
    font-weight: bolder;
    background: repeating-linear-gradient(-45deg, #f87171, #f87171 10px, white 10px, white 20px) !important;
}


.not_ranged_day,
.passed_day {
    color: rgb(83, 83, 83);
    cursor: context-menu;
}

.today {
    border: 3px solid rgb(255, 255, 255) !important;
    cursor: context-menu;
}

.text_color_success {
    color: #4ADE80;
}

.text_color_error {
    color: #e93f2c;
}

.text_color_warning {
    color: #F1C40F;
}

.disabled{
    color: #928f70 !important;
}

.admin_toggled {
    background-color: #30353d !important;
}

.tooltip-mvc {
    position: a;
    cursor: help;
}

.tooltip-mvc:hover::after {
    content: attr(tooltip-mvc);
    font-size: 18px;
    position: absolute;
    top: -1;
    right: 1;
    background-color: #c2d481;
    padding: 1px;
    color: rgb(63, 63, 63);
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    border-radius: 10px;
    z-index: 10;
}

@media (max-width: 975px) {
    .responsive_table {
        font-size: 1.5vw;
    }
    .tooltip-mvc:hover::after {
    content: attr(tooltip-mvc);
    font-size: 18px;
    position: absolute;
    top: -1;
    right: 1;
    background-color: #f2f89c;
    padding: 1px;
    color: rgb(85, 85, 85);
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 200px;
    border-radius: 10px;
    z-index: 10;
    }

}

@media (max-width: 650px) {
    .grid_style {
        display: grid !important;
    }

    .grid_style_legend {
        display: grid !important;
        justify-items: start;
    }

    .items_spacing_y {
        margin: 2px;
    }
}
</style>