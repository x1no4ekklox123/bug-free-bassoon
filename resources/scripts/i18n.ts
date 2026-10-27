import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend, { HttpBackendOptions } from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';
import { Locale } from 'date-fns';
import enUS from 'date-fns/locale/en-US';
import ru from 'date-fns/locale/ru';

// Every namespace maps to resources/lang/{locale}/{namespace}.php. All of them are loaded in
// a single request before the application renders, so components never suspend on them.
export const NAMESPACES = [
    'common',
    'navigation',
    'authentication',
    'dashboard',
    'account',
    'activity',
    'activitylog',
    'overview',
    'files',
    'databases',
    'backups',
    'schedules',
    'users',
    'network',
    'startup',
    'settings',
    'features',
] as const;

const DATE_LOCALES: Record<string, Locale> = { en: enUS, ru };

interface ExtendedWindow extends Window {
    PterodactylUser?: { language?: string };
    SiteConfiguration?: { locale?: string };
}

const { PterodactylUser, SiteConfiguration } = window as ExtendedWindow;

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = module.hot ? Date.now().toString(16) : process.env.WEBPACK_BUILD_HASH;

export const i18nReady = i18n
    .use(I18NextMultiloadBackendAdapter)
    .use(initReactI18next)
    .init({
        debug: process.env.DEBUG === 'true',
        lng: PterodactylUser?.language || SiteConfiguration?.locale || 'en',
        fallbackLng: 'en',
        load: 'languageOnly',
        ns: [...NAMESPACES],
        defaultNS: 'common',
        // Lets the multiload adapter batch every namespace (and the fallback) into one request.
        maxParallelReads: 64,
        keySeparator: '.',
        backend: {
            backend: I18NextHttpBackend,
            backendOption: {
                loadPath: '/locales/locale.json?locale={{lng}}&namespace={{ns}}',
                queryStringParams: { hash },
                allowMultiLoading: true,
            } as HttpBackendOptions,
        } as Record<string, any>,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    });

const setDocumentLanguage = (language: string) => document.documentElement.setAttribute('lang', language);

i18n.on('languageChanged', setDocumentLanguage);
i18nReady.then(() => setDocumentLanguage(i18n.resolvedLanguage || i18n.language));

/**
 * Returns the date-fns locale for the active language, pass it to every date-fns
 * formatting call that produces words (formatDistanceToNow, format with month names...).
 */
export const dateLocale = (): Locale => DATE_LOCALES[i18n.resolvedLanguage || i18n.language] || enUS;

export default i18n;
