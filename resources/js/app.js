import './bootstrap';
import '../css/app.scss';

import {createApp, h} from 'vue';
import {createInertiaApp, usePage} from '@inertiajs/inertia-vue3';
import {InertiaProgress} from '@inertiajs/progress';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy/dist/vue.m';
import {library} from '@fortawesome/fontawesome-svg-core';
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import translate from "@/VueTranslation/Translation";

import moment from "moment/min/moment-with-locales";

import {
    faGauge as fadGauge,
    faShelves as fadShelves,
    faPlus as fadPlus,
    faMagnifyingGlass as fadMagnifyingGlass,
    faCircleCheck as fadCircleCheck,
    faCircleXmark as fadCircleXmark,
    faWarehouse as fasWareHouse,
    faUser as fasUser,
    faCircleInfo as falCircleInfo,
    faChevronRight,
    faChevronDown,
    faList,
    faEdit,
    faCar,
    faDatabase,
    faArrowUpRightFromSquare,
    faCircleUser,
    faTrash as fadTrash,
    faListCheck,
    faUserGear,
    faArrowsLeftRight,
    faSitemap,
    faEarthEurope,
    faKey,
    faRotate,
    faTag,
    faEye,
    faEyeSlash,
    faGear,
    faArrowLeft,
    faChevronUp,
    faMinus,
    faArrowTurnDownRight,
    faAsterisk,
    faCircle,
    faExclamationCircle,
    faGlobe,
    faListDropdown,
    faEngine,
    faListTree,
} from "@fortawesome/pro-duotone-svg-icons";
import {
    faAngleRight as falAngleRight,
    faAngleLeft as falAngleLeft,
    faWrench as falWrench,
    faClose as falClose,
} from "@fortawesome/pro-light-svg-icons";
import {
    faTrash as falTrash,
    faTriangleExclamation as falTriangleExclamation,
} from "@fortawesome/pro-solid-svg-icons";
import {Inertia} from "@inertiajs/inertia";
import {plugin as formkitPlugin, defaultConfig} from '@formkit/vue'
import {it} from '@formkit/i18n';
import {generateClasses} from "@formkit/themes";
import theme from '../../theme';
import formkitInertify from 'formkit-inertify';
import "vue-multiselect/dist/vue-multiselect.css";
import {NDialogProvider} from "naive-ui";

library.add(faListTree,faEngine,faListDropdown,faGlobe,faExclamationCircle,faCircle,faAsterisk,faArrowTurnDownRight,faMinus,faChevronUp,faArrowLeft,faGear,faEye,faEyeSlash,faTag,faRotate,faKey,faEarthEurope, faSitemap, faArrowsLeftRight, faUserGear, faListCheck, fadTrash, faCircleUser, faArrowUpRightFromSquare, faDatabase, faCar, faList, faChevronDown, faEdit, fadGauge, faChevronRight, fadShelves, fadPlus, fadMagnifyingGlass, fadCircleCheck, fadCircleXmark, falAngleRight, falAngleLeft, falWrench, falTrash, falTriangleExclamation, fasWareHouse, fasUser, falCircleInfo, falClose);

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

window.inertiaEventsCount = {
    navigateCount: 0,
    successCount: 0,
    errorCount: 0,
}
const translateEntity = (value, lang, isGet = true) => {
    if (value instanceof Object) {
        if (lang) {
            if (value[lang]) {
                return value[lang]
            } else {
                if (isGet) {
                    value[lang] = value[usePage().props.value.defaultLang];
                } else {
                    value[lang] = '';
                }
                return value[lang]
            }
        }
        if(value[usePage().props.value.currentLang] && value[usePage().props.value.currentLang] != ''){
            return value[usePage().props.value.currentLang];
        }
        else {
            return value[usePage().props.value.defaultLang]
        }
    }
    return value;
}
const regexInput = (event, pattern, callback = null) => {
    let result = pattern.test(event.key);
    if (!result) {
        event.preventDefault();
        return false;
    }
    if (callback) {
        callback();
    }
    return true;
}
window.$regexInput = regexInput
window.$trans = translate;
window.getTranslatedEntity = translateEntity
const options = {
    locales: {it},
    locale: 'it',
    config: {
        classes: generateClasses(theme),
        plugins: [formkitInertify],
    }
};
moment.locale(options.locale)

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({el, app, props, plugin}) {
        const clickOutside = {
            beforeMount: (el, binding) => {
                el.clickOutsideEvent = event => {
                    if (!(el == event.target || el.contains(event.target))) {
                        binding.value();
                    }
                };
                document.addEventListener("click", el.clickOutsideEvent);
            },
            unmounted: el => {
                document.removeEventListener("click", el.clickOutsideEvent);
            },
        };

        const application = createApp({
            render: () => h(NDialogProvider, [h(app, props),]),
            mounted() {
                Inertia.on('navigate', () => window.inertiaEventsCount.navigateCount++);
                Inertia.on('success', () => window.inertiaEventsCount.successCount++);
                Inertia.on('error', () => window.inertiaEventsCount.errorCount++);
            },
        })
            .use(plugin)
            .directive("clickOut", clickOutside)
            .use(formkitPlugin, defaultConfig(options))
            .use(ZiggyVue, Ziggy)
            .component('font-awesome-icon', FontAwesomeIcon)
            .mixin({
                methods: {
                    __: translate,
                    $trans: translate,
                    getTranslatedEntity: translateEntity,
                    regexInput: (event, pattern, callback = null) => {
                        let result = pattern.test(event.key);
                        if (!result) {
                            event.preventDefault();
                            return false;
                        }
                        if (callback) {
                            callback();
                        }
                        return true;
                    },
                }
            });
        application.config.globalProperties.$filters = {
            timeAgo(date) {
                return moment(date).fromNow()
            },

        }

        const meta = document.createElement('meta')
        meta.name = 'naive-ui-style'
        document.head.appendChild(meta)

        return application
            .mount(el);
    },

});


InertiaProgress.init({color: '#4B5563'});
