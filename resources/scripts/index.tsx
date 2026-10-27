import React from 'react';
import ReactDOM from 'react-dom';
import App from '@/components/App';
import { setConfig } from 'react-hot-loader';
import { i18nReady } from './i18n';

// Prevents page reloads while making component changes which
// also avoids triggering constant loading indicators all over
// the place in development.
//
// @see https://github.com/gaearon/react-hot-loader#hook-support
setConfig({ reloadHooks: false });

// Translations are loaded before the first render; a failed request still renders the app.
i18nReady.finally(() => {
    ReactDOM.render(<App />, document.getElementById('app'));
});
