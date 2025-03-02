<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, reactive, onMounted, defineEmits } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import ModalInfoHolidays from '@/Components/ModalInfoHolidays.vue';

const loading = ref(true);
const user = ref(null);
const year = ref(null);
const holidays_data = reactive({});
const calendar_data = reactive({});
const requested_holidays = reactive([]);
const consulted_year = ref(null);
const consulted_user = ref(null);
const consulted_user_id = ref(null);
const week_days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb', 'Dom'];
const requesting_holidays = ref(false);
const modal_info_holidays = reactive({
    title: '',
    state: '',
    request_date: '',
    validation_comment: '',
    id: 0,
    cancel_holidays: false
});
const show_modal_info_holidays = ref(false);
const toast_options = {
    closeOnClick: true,
    toastStyle: {
        marginRight: '',
        backgroundColor: '#374151',
        color: '#DFECEC',
        borderRadius: '4px',
        border: '1px solid #9CA3AF'
    }
}
const toast_warning_options = {
    position: toast.POSITION.TOP_CENTER,
                    closeOnClick: true,
                    multiple: false,
                    toastStyle: {
                        width: '350px',
                        height: '50px',
                        marginTop: '50px',
                        fontSize: '1rem',
                        backgroundColor: '#364459',
                        color: '#DFECEC',
                        borderRadius: '5px',
                        border: '2px solid #9CA3AF'
                    }
}
const toast_success_options = {
    position: toast.POSITION.TOP_CENTER,
                    closeOnClick: true,
                    toastStyle: {
                        width: '350px',
                        height: '50px',
                        fontSize: '1rem',
                        backgroundColor: '#364459',
                        color: '#DFECEC',
                        borderRadius: '5px',
                        border: '2px solid #07bc0c'
                    }
}
const toast_error_options = {
    position: toast.POSITION.TOP_CENTER,
                    closeOnClick: true,
                    toastStyle: {
                        width: '350px',
                        height: '50px',
                        padding: '15px',
                        fontSize: '1rem',
                        backgroundColor: '#364459',
                        color: '#DFECEC',
                        borderRadius: '5px',
                        border: '2px solid #e74c3c'
                    }
}
/* #07bc0c green */
/* #e74c3c red */

const formatDateFromString = (date_string) => {
    if(date_string) {
        let dateObject = new Date(date_string);
        return `${dateObject.getFullYear()}-${dateObject.getMonth() + 1}-${dateObject.getDate()}`;
    } else {
        return "No disponible.";
    }
}

const fetchUser = async () => {
    await axios.get('/api/user')
        .then(response => {
            //console.log(response.data); // This will be your authenticated user
            user.value = response.data;
            console.log(user.value);
        })
        .catch(error => {
            console.error('Error fetching user:', error);
        });
};

const fetchHolidaysData = async (consulted_year = null, consulted_user_id = null) => {
    //console.log('Consulting vacations data for: Year->' + consulted_year + ' User-> ' + consulted_user_id);
    year.value = consulted_year === null ? new Date().getFullYear() : consulted_year
    let url = (consulted_user_id === null ? 'api/user/vacations/' + year.value : 'api/user/vacations/' + year.value + '/' + consulted_user_id);
    consulted_year = year.value;
    await axios.get(url)
        .then((response) => {
            Object.assign(holidays_data, response.data)
        })
        .catch((error) => { console.error(error) });
}

const fetchCalendarData = async (consulted_year = null, consulted_user_id = null) => {
    //console.log('Consulting calendar for: Year->' + consulted_year + ' User-> ' + consulted_user_id);
    year.value = consulted_year === null ? new Date().getFullYear() : consulted_year
    let url = (consulted_user_id === null ? 'api/calendar/' + year.value : 'api/calendar/' + year.value + '/' + consulted_user_id);
    consulted_year = year.value;
    await axios.get(url)
        .then((response) => {
            Object.assign(calendar_data, response.data)
        })
        .catch((error) => { console.error(error) });
}

const checkStatus = () => {
    console.log(user.value);
    console.log(holidays_data);
    console.log(calendar_data);
};

const selectDay = (day, key, month) => {
    console.log("ENTRA -> 1");
    if ((day.free_day != 1 || day.work_on_freedays) && day.day_state != 0 && day.holiday == 0 && consulted_user_id.value == null) {
        console.log('ENTRA -> 2')
        if (holidays_data.holiday_hours_to_be_requested.toString().endsWith('.00')) {
            holidays_data.holiday_hours_to_be_requested = parseInt(holidays_data.holiday_hours_to_be_requested.toString().slice(0, -3), 10);
        }
        if (holidays_data.holiday_hours_to_be_requested >= (day.range_working_hours / 2) || day.day_selected == 1) {
            console.log('ENTRA -> 3');
            if(holidays_data.pending_holidays_total_to_be_requested <= 0) {
                console.log('ENTRA -> 3.1');
                toast.warning('No te quedan más días por solicitar.', toast_options/*  toast_warning_options */);
                return;
            }
            day.day_selected = (day.day_selected ? 0 : 1);
            if (day.day_selected) {
                console.log('ENTRA -> 3.2');
                requested_holidays.push(key);
                holidays_data.holiday_hours_to_be_requested -= parseFloat(day.range_working_hours);
                holidays_data.pending_holidays_total_to_be_requested -= (parseFloat(day.range_working_hours) / holidays_data.current_working_hours);
            } else {
                const index = requested_holidays.indexOf(key);
                if (index > -1) {
                    requested_holidays.splice(index, 1);
                }
                holidays_data.holiday_hours_to_be_requested += parseFloat(day.range_working_hours);
                holidays_data.pending_holidays_total_to_be_requested += (parseFloat(day.range_working_hours) / holidays_data.current_working_hours);
            }
        } else {
            toast.warning('No te quedan más días por solicitar.', toast_options/* toast_warning_options */);
        }
    } else if (day.holiday > 0) {
        console.log('ENTRA -> 4');
        modal_info_holidays.title = day.day + " de " + month.month_name;
        modal_info_holidays.state = day.holiday_info.status_type == 2 ? "Confirmada." :
            day.holiday_info.status_type == 1 ? "Pendiente." : 
            day.holiday_info.status_type == 3 ? "Rechazada" :
            "Sin estado.";
        modal_info_holidays.request_date = day.holiday_info.created_at;
        console.log(modal_info_holidays.request_date);
        modal_info_holidays.id = day.holiday_info.id;
        modal_info_holidays.validation_comment =day.holiday_info.validation_comment;
        show_modal_info_holidays.value = true;
    }
}

const requestHolidays = async (event) => {
    // Prevent default form submission if this is being called in a form context
    if (event) {
        event.preventDefault();
    }

    requesting_holidays.value = true;

    try {
        const response = await axios.post("/vacations/request", {
            requested_holidays_array: requested_holidays,
            year_passed: year.value,
        });
        console.log(response);
        // Display the success toast
        toast.success(`${response.data.text}`, toast_options);

        if (response.data.calendar_data !== undefined) {
            Object.keys(calendar_data).forEach((key) => delete calendar_data[key]);
            Object.assign(calendar_data, response.data.calendar_data);

            Object.keys(holidays_data).forEach((key) => delete holidays_data[key]);
            Object.assign(holidays_data, response.data.holidays_data);

            requested_holidays.splice(0, requested_holidays.length);
            console.log(requested_holidays.length);
        }
    } catch (error) {
        // Handle error (optional)
        console.error("Error requesting holidays:", error);
        toast.error("There was an error requesting your holidays.", toast_options);
    } finally {
        requesting_holidays.value = false;
    }
};

const cancelHolidays = async (id) => {
    modal_info_holidays.cancel_holidays = true;
    await axios.post("/vacations/delete", {id: id, user: user.value})
    .then((response) => {
        console.log(response);
        if(response.data.status == "Success"){
            
            if (response.data.calendar_data != undefined) {
                Object.keys(calendar_data).forEach(key => delete calendar_data[key]);
                Object.assign(calendar_data, response.data.calendar_data);
                Object.keys(holidays_data).forEach(key => delete holidays_data[key]);
                Object.assign(holidays_data, response.data.holidays_data);
                requested_holidays.splice(0, requested_holidays.length);
            }

            toast.success(response.data.text, toast_options/* toast_success_options */);

            modal_info_holidays.cancel_holidays = false;
            show_modal_info_holidays.value = false;
        } else if(response.data.status == "Error") {
            toast.error(response.data.text, toast_options/* toast_error_options */)
            if (response.data.calendar_data != undefined) {
                Object.keys(calendar_data).forEach(key => delete calendar_data[key]);
                Object.assign(calendar_data, response.data.calendar_data);
                Object.keys(holidays_data).forEach(key => delete holidays_data[key]);
                Object.assign(holidays_data, response.data.holidays_data);
                requested_holidays.splice(0, requested_holidays.length);
            }
            modal_info_holidays.cancel_holidays = false;
            show_modal_info_holidays.value = false;
        } 
    });
}

const getHolidaysToBeConfirmed = async () => {
    await axios.get("api/vacations_to_be_confirmed")
    .then((response => {
        console.log(response.data)
        }
    ));
}

const openModalInfoHolidays = () => {
    show_modal_info_holidays.value = true;
}

const closeModalInfoHolidays = () => {
    console.log("MODAL ON CLOSE");
    show_modal_info_holidays.value = false;
};

const load_template = async () => {
    loading.value = false;
}

onMounted(async () => {
    await fetchUser();
    await fetchCalendarData();
    await fetchHolidaysData();
    await load_template();
});

</script>

<template>

    <Head title="Vacaciones" />

    <AuthenticatedLayout>
        <template #header>
        
            <h2 class="text-2xl text-gray-800 dark:text-gray-200 leading-tight" style="font-family: 'Abel', sans-serif;">Bienvenido a Vacaciones
            </h2>
        </template>
<div v-if="!loading">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-3 px-6 text-gray-900 dark:text-gray-100"> <!-- Vacations header info (user-year) -->
                        <p v-if="user" class="mt-2 mb-4 text-xl text-gray-300" style="font-family: 'Abel', sans-serif;"> Vacaciones de <span class="text-white">{{ user.name }}</span> - {{ year }}</p>
                        <p v-else class="mt-2 mb-4 text-xl" style="font-family: 'Abel', sans-serif;">Solicitud de vacaciones - {{ year }}</p>
                    </div>
                    <div class="flex justify-around mb-5"><!-- Vacations DATA container -->
                        <div class="flex justify-around items-center mx-2 grid_style"><!-- Vacations DATA div -->
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" v-if="holidays_data.hours_per_contract != undefined" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="holidays_data.hours_per_contract.toFixed(2) + ' horas.'">
                                    Vacaciones:<span class="ml-2">{{ (holidays_data.hours_per_contract / holidays_data.current_working_hours).toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" v-if="holidays_data.accumulated_holidays_days != undefined" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="(holidays_data.accumulated_holidays_days * holidays_data.current_working_hours).toFixed(2) + ' horas.'">
                                    Acumuladas:<span class="ml-2">{{ holidays_data.accumulated_holidays_days.toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" v-if="holidays_data.extra_holidays_days != undefined" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="(holidays_data.extra_holidays_days * holidays_data.current_working_hours).toFixed(2) + ' horas.'">
                                    Extras:<span class="ml-2">{{ holidays_data.extra_holidays_days.toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" v-if="holidays_data.confirmed_holiday_hours != undefined" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="holidays_data.confirmed_holiday_hours.toFixed(2) + ' horas.'">
                                    Validadas:<span class="ml-2">{{ Math.round(holidays_data.confirmed_holiday_hours / holidays_data.current_working_hours).toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="'Disfrutadas: ' + holidays_data.enjoyed_holidays_days.toFixed(0) + ' días. &nbsp; Pagadas: ' + holidays_data.paid_holidays_days +' días.'">
                                    Disfrutadas / Pagadas:<span class="ml-2">{{ holidays_data.enjoyed_holidays_days + holidays_data.paid_holidays_days }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="holidays_data.pending_holiday_hours_to_be_enjoyed.toFixed(2) + ' horas.'">
                                    Pe. Disfrutar:<span class="ml-2">{{ Math.round(holidays_data.pending_holiday_hours_to_be_enjoyed / holidays_data.current_working_hours).toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc=" holidays_data.pending_holiday_hours_to_be_confirmed.toFixed(2) + ' horas.'">
                                    Pe. Confirmar:<span class="ml-2">{{ Math.round(holidays_data.pending_holiday_hours_to_be_confirmed / holidays_data.current_working_hours).toFixed(0) }}</span>
                                </div>
                            </div>
                            <div class="col-md-12 flex justify-around flex-wrap mb-2 m-1 items_spacing_y" style="font-family: 'Abel', sans-serif;">
                                <div class="tooltip-mvc text-xl p-1 text-gray-200 bg-gray-700 text-center flex items-center justify-center w-full rounded-[5px] z-10" :tooltip-mvc="(holidays_data.pending_holidays_total_to_be_requested * holidays_data.current_working_hours).toFixed(2) + ' horas.'">
                                    Pe. Solicitar:<span class="ml-2">{{ Math.round(holidays_data.pending_holidays_total_to_be_requested).toFixed(0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="flex justify-end flex-wrap text-sm mx-2 my-2 text-gray-200 opacity-75 italic">*vacaciones calculadas con el contrato actual de {{ holidays_data.current_working_hours }} horas.</span>
                    <div class="flex justify-around mb-5 grid_style_legend"> <!-- Legend container -->
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 today rounded-md"><!-- Today -->
                            </div>
                            <div>
                                <p class="text-white">Hoy.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 bg-yellow-100 rounded-md"><!-- Pending holidays -->
                            </div>
                            <div>
                                <p class="text-white">Vacaciones pendientes.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 bg-green-400 rounded-md"><!-- Confirmed holidays -->
                            </div>
                            <div>
                                <p class="text-white">Vacaciones confirmadas.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 bg-red-400 rounded-md"><!-- Free days -->
                            </div>
                            <div>
                                <p class="text-white">Día festivo / Fin de semana.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 work_on_freedays rounded-md"><!-- Work on free days -->
                            </div>
                            <div>
                                <p class="text-white">Fin de semana laborable.</p>
                            </div>
                        </div>
                        <div class="flex justify-around mx-2 items-center items_spacing_y"> <!-- Legend div -->
                            <div class="w-6 h-6 mr-1.5 locked_already_requested_day rounded-md"><!-- Work on free days -->
                            </div>
                            <div>
                                <p class="text-white">No disponible.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 flex justify-around flex-wrap text-white mb-8"><!-- calendarios - Calendar outer div -->
                        <div class="relative w-72 box-border shadow-lg rounded-lg overflow-hidden border border-solid border-gray-600 m-2" :class="[month.previous_month ? 'past_month' : '']" v-for="(month, key) in calendar_data"
                        :key="key"> <!-- calendar -->
                            <div class="w-full text-center text-[22px] m-0 text-gray-200 bg-gray-700"> <!-- calendar__mes - Month name - Header -->
                                {{month.month_name}}
                            </div>
                            <div class="p-5 grid grid-cols-[repeat(auto-fit,minmax(25px,1fr))] gap-2.5 box-border">
                                <!-- calendar__date - Month body -->
                                <div v-for="day in week_days" :key="day"
                                    class="flex items-center justify-center h-[25px] font-semibold text-white"> <!-- calendar__day - Week headings - v-for -->
                                    <div>{{ day }}</div>
                                </div>
                                <div @click="(!days.day.locked_already_requested_day && days.day.range_working_hours != 0) || user.value.admin ? selectDay(days, key, month) : false" v-for="(days, key) in month.days" :key="key" class="flex items-center justify-center h-6 text-gray-300 cursor-pointer rounded-md" :class="[month.prevous_month ? 'past_month_text' : '',
                                                  (days.free_day && !days.work_on_freedays) ? 'free_day' : '',
                                                  (days.free_day && days.work_on_freedays) ? 'work_on_freedays' : '',
                                                  days.holiday == 2 ? 'confirmed_holiday' : '',
                                                  days.day_selected ? 'day_selected' : '',
                                                  days.locked_already_requested_day && days.holiday == 0 ? 'locked_already_requested_day' : '',
                                                  days.range_working_hours == 0 ? 'not_ranged_day' : '',
                                                  days.holiday == 1 ? 'pending_holiday' : '',
                                                  days.day_state == 1 ? 'today' : !month.previous_month && days.day_state == 0 ? 'passed_day' : '']"> <!-- calendar__number - actual month dates-->
                                    <div :class="[(!(month.prevous_month) && !(days.holiday == 2) && !days.day_selected && !(days.locked_already_requested_day && days.holiday == 0)
                                                    && !(days.range_working_hours == 0) && !(days.holiday == 1) && !(days.day_state == 1) && !(!month.prevous_month && days.day_state == 0)) ? 'rounded hover:bg-green-100 hover:opacity-2 hover:text-green-400 px-1' : '']
                                                    ">{{ days.day }}</div>
                                </div>
                            </div>
                        </div> <!-- v-for - each Month div -->
                    </div>
                    <!-- <div class="flex justify-around mb-5"> --> <!-- Vacations DATA container -->
                        <!-- <div class="flex justify-around items-center mx-2" v-if="(Date.parse(year?.value + '-01-01') >= Date.parse('2023-01-01')) || user.is_admin"> --><!-- Vacations DATA div -->
<!--                             <p class="text-white text-xl" style="font-family: 'Abel', sans-serif;">Ver año: </p>
                            <a class="cursor-pointer text-xl text-[#8193a5] font-bold ml-2.5" style="font-family: 'Abel', sans-serif;">{{ year - 1}}</a>
                            <a class="cursor-pointer text-xl text-[#8193a5] font-bold ml-2.5" style="font-family: 'Abel', sans-serif;">{{ year }}</a>
                            <a class="cursor-pointer text-xl text-[#8193a5] font-bold ml-2.5" style="font-family: 'Abel', sans-serif;">{{ year + 1 }}</a>
                        </div>
                    </div> -->
                    <div class="flex w-full rounded-lg">
                        <button class=" w-full rounded-lg btn btn-success text-2xl py-2 mx-2 mb-5 text-gray-200 bg-green-800 hover:bg-green-600" style="font-family: 'Abel', sans-serif;" :disabled="requesting_holidays" v-if="requested_holidays.length > 0" @click="requestHolidays()">Solicitar vacaciones</button>
                    </div>
                </div>
                <ModalInfoHolidays v-if="show_modal_info_holidays" :isOpen="show_modal_info_holidays" @modal-close="closeModalInfoHolidays" name="modal-info-holidays" role="document">
                    <template v-slot:modal-header>
                        <p><span class="font-bold text-gray-300">Día:</span> {{ modal_info_holidays.title }} </p>
                    </template>
                    <template v-slot:modal-content>
                        <p><span class="font-bold text-gray-300">Estado solicitud: </span><span class="text-color-white p-1.5" :class="[(modal_info_holidays.state == 'Confirmada.') ? 'text_color_success' : '',
                            (modal_info_holidays.state == 'Rechazada.') ? 'text_color_error' : '',
                            (modal_info_holidays.state == 'Pendiente.') ? 'text_color_warning' : ''
                        ]"> {{ modal_info_holidays.state }} </span></p>
                        <p class="text-color-white"> <span class="font-bold text-gray-300">Fecha solicitud:</span> {{ formatDateFromString(modal_info_holidays.request_date) }}</p>
                        <p class="text-color-white" v-if="modal_info_holidays.validation_comment"><span class="font-bold text-gray-300">Comentario: </span><span class="text-green-100">{{ modal_info_holidays.validation_comment }}</span></p>
                        <div class="flex justify-center items-center min-h-24 pos-relative" v-if="modal_info_holidays.cancel_holidays">
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
                        <div class="pt-10 pl-8">
                            <button type="button" @click="closeModalInfoHolidays" class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">Cerrar</button>
                            <button type="button" class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900" @click="cancelHolidays(modal_info_holidays.id)">Cancelar vacaciones</button>
                        </div>
                    </template>
                </ModalInfoHolidays>
                <div><button @click="getUser">
                            <p class="text-green-200">Send request for autenticated user.</p>
                        </button></div>

                <div><button @click="checkStatus">
                        <p class="text-green-200">Send to check data.</p>
                    </button></div>

                <div><button @click="getHolidaysToBeConfirmed">
                    <p class="text-green-200">Get to be confirmed holidays.</p>
                </button></div>
            </div>
        </div>
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
    color:#4e4e50 !important;
}
.day_selected {
    background-color: #57ddff;
    color:#4e4e50 !important;
}
.locked_already_requested_day {
    cursor: context-menu;
    color:#4e4e50 !important;
    text-shadow: 0px 0px 2px #ffffff;
    font-weight:bolder;
    background: repeating-linear-gradient(-45deg, #f87171, #f87171 10px, white 10px, white 20px) !important;
}
.not_ranged_day, .passed_day{
    color: rgb(83, 83, 83);
    cursor: context-menu;
}
.pending_holiday{
    background-color: #FEF9C3;
    color:#4e4e50 !important;
}
.today{
    border: 2px solid rgb(175, 175, 175) !important;
    cursor: context-menu;
}

.tooltip-mvc {
    position: relative;
    cursor: help;
}
.tooltip-mvc:hover::after {
    content: attr(tooltip-mvc);
    font-size: 18px;
    position: absolute;
    top: 55px;
    right: 0;
    background-color: #343a40;
    padding: 5px;
    color: white;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    border-radius: 15px;
    z-index: 10;
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

@media (max-width: 950px) {
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


<!-- 
// Function to get the value of a specific cookie by name
function getCookie(name) {
    let cookieValue = null;
    if (document.cookie && document.cookie !== '') {
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i].trim();
            // If the cookie starts with the desired name, extract its value
            if (cookie.startsWith(name + '=')) {
                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                break;
            }
        }
    }
    return cookieValue;
} 
-->