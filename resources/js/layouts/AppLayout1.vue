<template>
    <!--wrapper-->
    <div :class="['wrapper', active ? 'toggled' : '']">
        <Sidebar @toggle-sidebar="toggleSidebar" />
        <header>
            <div class="topbar d-flex align-items-center">
                <nav class="navbar navbar-expand">
                    <div @click="toggleSidebar" class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
                    <div class="top-menu ms-auto">
                        <ul class="navbar-nav align-items-center">
                            <li class="nav-item dropdown dropdown-large">
                                <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">7</span>
                                    <i class='bx bx-bell'></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:;">
                                        <div class="msg-header">
                                            <p class="msg-header-title">Notifications</p>
                                            <p class="msg-header-clear ms-auto">Marks all as read</p>
                                        </div>
                                    </a>
                                    <div class="header-notifications-list" style="overflow-y: scroll;">
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-primary text-primary"><i class="bx bx-group"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">New Customers<span class="msg-time float-end">14 Sec ago</span></h6>
                                                    <p class="msg-info">5 new user registered</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-danger text-danger"><i class="bx bx-cart-alt"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">New Orders <span class="msg-time float-end">2 min ago</span></h6>
                                                    <p class="msg-info">You have recived new orders</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-success text-success"><i class="bx bx-file"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">24 PDF File<span class="msg-time float-end">19 min ago</span></h6>
                                                    <p class="msg-info">The pdf files generated</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-warning text-warning"><i class="bx bx-send"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">Time Response <span class="msg-time float-end">28 min ago</span></h6>
                                                    <p class="msg-info">5.1 min avarage time response</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-info text-info"><i class="bx bx-home-circle"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">New Product Approved <span class="msg-time float-end">2 hrs ago</span></h6>
                                                    <p class="msg-info">Your new product has approved</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-danger text-danger"><i class="bx bx-message-detail"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">New Comments <span class="msg-time float-end">4 hrs ago</span></h6>
                                                    <p class="msg-info">New customer comments recived</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-success text-success"><i class='bx bx-check-square'></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">Your item is shipped <span class="msg-time float-end">5 hrs ago</span></h6>
                                                    <p class="msg-info">Successfully shipped your item</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-primary text-primary"><i class='bx bx-user-pin'></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">New 24 authors<span class="msg-time float-end">1 day ago</span></h6>
                                                    <p class="msg-info">24 new authors joined last week</p>
                                                </div>
                                            </div>
                                        </a>
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-warning text-warning"><i class='bx bx-door-open'></i> </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">Defense Alerts <span class="msg-time float-end">2 weeks ago</span></h6>
                                                    <p class="msg-info">45% less alerts last 4 weeks</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <a href="javascript:;">
                                        <div class="text-center msg-footer">View All Notifications</div>
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <select v-model="season" id="season" @change="changeSeason" class="form-select form-select-sm" style="font-size: small;">
                                    <option value="">সিলেক্ট সিজন</option>
                                    <option value="2425">সিজন:২৪-২৫</option>
                                    <option value="2526">সিজন:২৫-২৬</option>
                                    <option value="2627">সিজন:২৬-২৭</option>
                                    <option value="2728">সিজন:২৭-২৮</option>
                                    <option value="2829">সিজন:২৮-২৯</option>
                                    <option value="2930">সিজন:২৯-৩০</option>
                                    <option value="3031">সিজন:৩০-৩১</option>
                                    <option value="3132">সিজন:৩১-৩২</option>
                                    <option value="3233">সিজন:৩২-৩৩</option>
                                    <option value="3334">সিজন:৩৩-৩৪</option>
                                    <option value="3435">সিজন:৩৪-৩৫</option>
                                </select>
                            </li>
                        </ul>
                    </div>
                    <div class="user-box dropdown">
                        <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" :title="user.name" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img :src="Avatar" class="user-img" alt="user avatar">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-user"></i><span>Profile</span></a></li>
                            <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-cog"></i><span>Settings</span></a></li>
                            <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-home-circle"></i><span>Dashboard</span></a></li>
                            <li><div class="dropdown-divider mb-0"></div></li>
                            <li><a class="dropdown-item" href="javascript:;" @click="logout"><i class='bx bx-log-out-circle'></i><span>Logout</span></a></li>
                        </ul>
                    </div>
                </nav>
            </div>
        </header>

        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <slot />
                <!--end row-->
            </div>
        </div>
        <!--end page wrapper -->

        <div class="overlay toggle-icon"></div>
        <footer class="page-footer">
            <p class="mb-0">Copyright © {{ new Date().getFullYear() }}. Developed by <a href="#" target="_blank">#</a>.</p>
        </footer>
    </div>
    <!--end wrapper-->
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue';
import { router, usePage } from '@inertiajs/vue3'
import { onMounted, watch,ref } from 'vue'
import { toast } from 'vue3-toastify'
import Avatar from '@/image/avatar.png';
import axios from 'axios';
import { route } from 'ziggy-js';

const page = usePage()
const user = page.props.auth.user

const active = ref(false)
const season = ref('')
const toggleSidebar = () => {
    active.value = !active.value
}

const changeSeason = async () => {
    if (!season.value) return
    try {
        await axios.post(route('dashboard.session_change'), { season: season.value })
        toast.success('সিজন সফলভাবে পরিবর্তন করা হয়েছে')
    } catch (error) {
        toast.error(error.response.data.message)
    }
}

const fetchSeason = async () => {
    try {
        const { data } = await axios.get(route('session.get'))
        season.value = data.season || ''
    } catch (error) {
        console.error(error)
    }
}

onMounted(() => {
    fetchSeason()
})

// Optional: Watch for flash changes
watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) toast.success(flash.success)
        if (flash.error) toast.error(flash.error)
        if (flash.warning) toast.warning(flash.warning)
        if (flash.info) toast.info(flash.info)
    },
    { immediate: true }
)

const logout = () => {
    router.post(route('logout'))
}

</script>
