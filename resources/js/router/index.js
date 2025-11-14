import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useLoading } from '@/composables/useLoading';

// Import pages
import Home from '@/pages/Home.vue';
import Login from '@/pages/auth/Login.vue';
import Register from '@/pages/auth/Register.vue';
import Dashboard from '@/pages/Dashboard.vue';
import Appointments from '@/pages/appointments/Index.vue';
import AppointmentCreate from '@/pages/appointments/Create.vue';
import AppointmentShow from '@/pages/appointments/Show.vue';
import Doctors from '@/pages/doctors/Index.vue';
import DoctorShow from '@/pages/doctors/Show.vue';
import DoctorSchedule from '@/pages/doctors/Schedule.vue';
import Clinics from '@/pages/clinics/Index.vue';
import ClinicShow from '@/pages/clinics/Show.vue';
import Profile from '@/pages/Profile.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta: { requiresAuth: false }
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { requiresAuth: false, guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { requiresAuth: false, guest: true }
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { requiresAuth: true }
    },
    {
        path: '/appointments',
        name: 'appointments.index',
        component: Appointments,
        meta: { requiresAuth: true }
    },
    {
        path: '/appointments/create',
        name: 'appointments.create',
        component: AppointmentCreate,
        meta: { requiresAuth: true }
    },
    {
        path: '/appointments/:id',
        name: 'appointments.show',
        component: AppointmentShow,
        meta: { requiresAuth: true }
    },
    {
        path: '/doctors',
        name: 'doctors.index',
        component: Doctors,
        meta: { requiresAuth: false }
    },
    {
        path: '/doctors/:id',
        name: 'doctors.show',
        component: DoctorShow,
        meta: { requiresAuth: false }
    },
    {
        path: '/doctor/schedule',
        name: 'doctor.schedule',
        component: DoctorSchedule,
        meta: { requiresAuth: true, doctorOnly: true }
    },
    {
        path: '/clinics',
        name: 'clinics.index',
        component: Clinics,
        meta: { requiresAuth: false }
    },
    {
        path: '/clinics/:id',
        name: 'clinics.show',
        component: ClinicShow,
        meta: { requiresAuth: false }
    },
    {
        path: '/profile',
        name: 'profile',
        component: Profile,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    const { startGlobalLoading } = useLoading();
    
    // Mostrar loading apenas se estivermos navegando entre páginas diferentes
    if (to.path !== from.path) {
        // Definir mensagens personalizadas baseadas na rota
        let title = 'Carregando...';
        let message = 'Preparando a página';
        
        if (to.name === 'appointments.index') {
            title = 'Carregando consultas...';
            message = 'Buscando seus agendamentos';
        } else if (to.name === 'appointments.create') {
            title = 'Novo agendamento...';
            message = 'Preparando formulário de agendamento';
        } else if (to.name === 'dashboard') {
            title = 'Carregando dashboard...';
            message = 'Preparando seu painel de controle';
        } else if (to.name === 'doctors.index') {
            title = 'Carregando médicos...';
            message = 'Buscando lista de médicos';
        } else if (to.name === 'clinics.index') {
            title = 'Carregando clínicas...';
            message = 'Buscando lista de clínicas';
        }
        
        startGlobalLoading(title, message);
    }
    
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else if (to.meta.doctorOnly && authStore.user?.user_type !== 'doctor') {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

router.afterEach(() => {
    const { stopGlobalLoading } = useLoading();
    // Para loading suave, adiciona um pequeno delay
    setTimeout(() => {
        stopGlobalLoading();
    }, 300);
});

export default router;