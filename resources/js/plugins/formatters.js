export default {
    install(app, options = {}) {
        const locale = options.locale || window.AppConfig?.locale || 'en-US'
        const currency = options.currency || window.AppConfig?.currency || 'USD'

        const formatters = {
            date(dateString, customLocale = null) {
                if (!dateString) return ''
                const date = new Date(dateString)
                return date.toLocaleDateString(customLocale || locale, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                })
            },

            dateTime(dateString, customLocale = null) {
                if (!dateString) return ''
                const date = new Date(dateString)
                return date.toLocaleString(customLocale || locale, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })
            },

            time(dateString, customLocale = null) {
                if (!dateString) return ''
                const date = new Date(dateString)
                return date.toLocaleTimeString(customLocale || locale, {
                    hour: '2-digit',
                    minute: '2-digit'
                })
            },

            currency(amount, customLocale = null, customCurrency = null) {
                return new Intl.NumberFormat(customLocale || locale, {
                    style: 'currency',
                    currency: customCurrency || currency
                }).format(amount || 0)
            },

            number(number, customLocale = null) {
                return new Intl.NumberFormat(customLocale || locale).format(number || 0)
            },

            percent(number, customLocale = null, decimals = 1) {
                return new Intl.NumberFormat(customLocale || locale, {
                    style: 'percent',
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }).format((number || 0) / 100)
            },

            getLocale() {
                return locale
            },

            getCurrency() {
                return currency
            }
        }

        app.config.globalProperties.$format = formatters

        app.provide('formatters', formatters)
        app.provide('locale', locale)
        app.provide('currency', currency)
    }
}
