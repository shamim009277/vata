<template>
    <div>
        <!-- Preloader -->
        <!-- <div v-if="loading" class="preloader">
            <div class="spinner"></div>
        </div> -->
        <!--wrapper-->
        <div class="wrapper">

            <Sidebar />

            <header>
                <div class="topbar d-flex align-items-center">
                    <nav class="navbar navbar-expand">
                        <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
                        <div class="top-menu ms-auto">
                            <ul class="navbar-nav align-items-center">
                                <li class="nav-item mobile-search-icon">
                                    <a class="nav-link" href="#"> <i class='bx bx-search'></i>
                                    </a>
                                </li>
                                <li class="nav-item dropdown dropdown-large">
                                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span
                                            class="alert-count">7</span>
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
                                                    <div class="notify bg-light-primary text-primary"><i
                                                            class="bx bx-group"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">New Customers<span
                                                                class="msg-time float-end">14 Sec
                                                                ago</span></h6>
                                                        <p class="msg-info">5 new user registered</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-danger text-danger"><i
                                                            class="bx bx-cart-alt"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">New Orders <span class="msg-time float-end">2
                                                                min
                                                                ago</span></h6>
                                                        <p class="msg-info">You have recived new orders</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-success text-success"><i
                                                            class="bx bx-file"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">24 PDF File<span class="msg-time float-end">19
                                                                min
                                                                ago</span></h6>
                                                        <p class="msg-info">The pdf files generated</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-warning text-warning"><i
                                                            class="bx bx-send"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Time Response <span
                                                                class="msg-time float-end">28 min
                                                                ago</span></h6>
                                                        <p class="msg-info">5.1 min avarage time response</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-info text-info"><i
                                                            class="bx bx-home-circle"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">New Product Approved <span
                                                                class="msg-time float-end">2 hrs ago</span></h6>
                                                        <p class="msg-info">Your new product has approved</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-danger text-danger"><i
                                                            class="bx bx-message-detail"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">New Comments <span class="msg-time float-end">4
                                                                hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">New customer comments recived</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-success text-success"><i
                                                            class='bx bx-check-square'></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Your item is shipped <span
                                                                class="msg-time float-end">5 hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">Successfully shipped your item</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-primary text-primary"><i
                                                            class='bx bx-user-pin'></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">New 24 authors<span
                                                                class="msg-time float-end">1 day
                                                                ago</span></h6>
                                                        <p class="msg-info">24 new authors joined last week</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="notify bg-light-warning text-warning"><i
                                                            class='bx bx-door-open'></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Defense Alerts <span
                                                                class="msg-time float-end">2 weeks
                                                                ago</span></h6>
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
                                <li class="nav-item dropdown dropdown-large">
                                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                                        role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span
                                            class="alert-count">8</span>
                                        <i class='bx bx-comment'></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:;">
                                            <div class="msg-header">
                                                <p class="msg-header-title">Messages</p>
                                                <p class="msg-header-clear ms-auto">Marks all as read</p>
                                            </div>
                                        </a>
                                        <div class="header-message-list" style="overflow-y: scroll; overflow-x: hidden;">
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-1.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Daisy Anderson <span
                                                                class="msg-time float-end">5 sec
                                                                ago</span></h6>
                                                        <p class="msg-info">The standard chunk of lorem</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-2.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Althea Cabardo <span
                                                                class="msg-time float-end">14
                                                                sec ago</span></h6>
                                                        <p class="msg-info">Many desktop publishing packages</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-3.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Oscar Garner <span class="msg-time float-end">8
                                                                min
                                                                ago</span></h6>
                                                        <p class="msg-info">Various versions have evolved over</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-4.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Katherine Pechon <span
                                                                class="msg-time float-end">15
                                                                min ago</span></h6>
                                                        <p class="msg-info">Making this the first true generator</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-5.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Amelia Doe <span class="msg-time float-end">22
                                                                min
                                                                ago</span></h6>
                                                        <p class="msg-info">Duis aute irure dolor in reprehenderit</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-6.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Cristina Jhons <span
                                                                class="msg-time float-end">2 hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">The passage is attributed to an unknown</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-7.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">James Caviness <span
                                                                class="msg-time float-end">4 hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">The point of using Lorem</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-8.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Peter Costanzo <span
                                                                class="msg-time float-end">6 hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">It was popularised in the 1960s</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-9.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">David Buckley <span
                                                                class="msg-time float-end">2 hrs
                                                                ago</span></h6>
                                                        <p class="msg-info">Various versions have evolved over</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-10.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Thomas Wheeler <span
                                                                class="msg-time float-end">2 days
                                                                ago</span></h6>
                                                        <p class="msg-info">If you are going to use a passage</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a class="dropdown-item" href="javascript:;">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-online">
                                                        <img src="assets/images/avatars/avatar-11.png" class="msg-avatar"
                                                            alt="user avatar">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="msg-name">Johnny Seitz <span class="msg-time float-end">5
                                                                days
                                                                ago</span></h6>
                                                        <p class="msg-info">All the Lorem Ipsum generators</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <a href="javascript:;">
                                            <div class="text-center msg-footer">View All Messages</div>
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <select @change="changeLang($event)" v-model="selectedLang" class="border rounded px-2 py-1">
                            <option value="bn" :selected="selectedLang === 'bn'">বাংলা</option>
                            <option value="en">English</option>
                          </select>
                        <div class="user-box dropdown">
                            <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="assets/images/avatars/avatar-2.png" class="user-img" alt="user avatar">
                                <div class="user-info ps-3">
                                    <p class="user-name mb-0">Pauline Seitz</p>
                                    <p class="designattion mb-0">Web Designer</p>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="javascript:;"><i
                                            class="bx bx-user"></i><span>Profile</span></a></li>
                                <li><a class="dropdown-item" href="javascript:;"><i
                                            class="bx bx-cog"></i><span>Settings</span></a></li>
                                <li><a class="dropdown-item" href="javascript:;"><i
                                            class='bx bx-home-circle'></i><span>Dashboard</span></a></li>
                                <!-- <li><a class="dropdown-item" href="javascript:;"><i
                                            class='bx bx-dollar-circle'></i><span>Earnings</span></a>
                                </li>
                                <li><a class="dropdown-item" href="javascript:;"><i
                                            class='bx bx-download'></i><span>Downloads</span></a>
                                </li> -->
                                <li>
                                    <div class="dropdown-divider mb-0"></div>
                                </li>
                                <li><a class="dropdown-item" href="javascript:;"><i
                                            class='bx bx-log-out-circle'></i><span>Logout</span></a></li>
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
                <p class="mb-0">Copyright © 2021. All right reserved.</p>
            </footer>
        </div>
        <!--end wrapper-->
    </div>
    
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue';
import { usePage } from '@inertiajs/vue3'
import { onMounted, watch, ref,computed } from 'vue'
import { toast } from 'vue3-toastify'
import { router } from '@inertiajs/vue3'

const loading = ref(false)
const page = usePage()

// প্রথম load এ preloader hide করা
// onMounted(() => {
//     setTimeout(() => {
//         loading.value = false
//     }, 800)
// })

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

const selectedLang = ref(page.props.locale);

const currentLangLabel = computed(() =>
  selectedLang.value === "bn" ? "বাংলা" : "English"
);

function changeLang(event) {
  const locale = event.target.value;
  //router.visit(route('set-locale', locale), { preserveState: false });
  router.visit(route('set-locale', locale), {
        preserveScroll: true,
        preserveState: false,  // ❌ make sure false
        replace: true          // replace history
    });
}

// Inertia progress bar এর সাথে preloader bind করা
router.on('start', () => {
    loading.value = true
})
router.on('finish', () => {
    setTimeout(() => {
        loading.value = false
    }, 500)
})
</script>

<style scoped>
/* Preloader */
.preloader {
    position: fixed;
    inset: 0;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 6px solid #e5e5e5;
    border-top-color: #3498db;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Wrapper & Layout */
.wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.page-wrapper {
    flex: 1;
    padding: 20px;
    background: #f5f6fa;
}

.page-footer {
    text-align: center;
    padding: 12px;
    background: #fff;
    border-top: 1px solid #ddd;
}
</style>
