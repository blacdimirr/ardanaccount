<link rel='stylesheet' href='{{asset('css/cookieconsent.css')}}' media="screen" />
<script src="{{ asset('js/cookieconsent.js') }}"></script>

<script>

<?php
    $setting= \App\Models\Utility::settings();
    $data=json_encode($setting);
?>
var data={!! json_encode($data) !!};
var parsed = JSON.parse(data);
const appLang = "{{ app()->getLocale() }}";

    let language_code = document.documentElement.getAttribute('lang');
    let languages = {};
    languages[language_code] = {
        consent_modal: {
            title: '{{ __('Hola') }}',
            description: '{{ __('Descripción') }}',
            primary_btn: {
                text: '{{ __('Texto del botón principal') }}',
                role: 'accept_all'
            },
            secondary_btn: {
                        text: '{{ __('Texto del botón secundario') }}',
                        role: 'accept_necessary'
                    }
                },
                settings_modal: {
                    title: '{{ __('Modal de configuración') }}',
                    save_settings_btn: '{{ __('Guardar configuración') }}',
                    accept_all_btn: '{{ __('Aceptar todo') }}',
                    reject_all_btn: '{{ __('Rechazar todo') }}',
                    close_btn_label: '{{ __('Cerrar') }}',
                    blocks: [{
                            title: '{{ __('Título del bloque') }}',
                            description: '{{ __('Descripción del bloque') }}'
                        },

                        {
                            title: '{{ __('Título') }}',
                            description: '{{ __('Descripción') }}',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: false
                            }
                        },
                    ]
                }
            };
            </script>
        <script>
            function setCookie(cname, cvalue, exdays) {
                const d = new Date();
                d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                let expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            }
            
            function getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            }
            
            
            // obtain plugin
            var cc = initCookieConsent();
            // run plugin with your configuration
            cc.run({
                current_lang: appLang,
                autoclear_cookies: true, // default: false
                page_scripts: true,
                // ...
                gui_options: {
                    consent_modal: {
                        layout: 'cloud', // box/cloud/bar
                        position: 'bottom center', // bottom/middle/top + left/right/center
                        transition: 'slide', // zoom/slide
                        swap_buttons: false // enable to invert buttons
                    },
                    settings_modal: {
                        layout: 'box', // box/bar
                        // position: 'left',           // left/right
                        transition: 'slide' // zoom/slide
                    }
                },
               
                onChange: function(cookie, changed_preferences) {},
                onAccept: function(cookie) {
                    if (!getCookie('cookie_consent_logged')) {
                        var cookie = cookie.level;
                        $.ajax({
                            url: '{{ route('cookie-consent') }}',
                            datType: 'json',
                            data: {
                                cookie: cookie,
                            },
                        })
                        setCookie('cookie_consent_logged', '1', 182, '/');
                    }
                },
                
                languages: {
                    [appLang]: {
                        consent_modal: {
                            title: parsed.cookie_title,
                            description: parsed.cookie_description + ' <button type="button" data-cc="c-settings" class="cc-link">{{ __('Déjame elegir') }}</button>',
                            primary_btn: {
                                text: '{{ __('Aceptar todo') }}',
                                role: 'accept_all' // 'accept_selected' or 'accept_all'
                            },
                            secondary_btn: {
                                text: '{{ __('Rechazar todo') }}',
                                role: 'accept_necessary' // 'settings' or 'accept_necessary'
                            },
                        },
                        settings_modal: {
                            title: '{{ __('Preferencias de cookies') }}',
                            save_settings_btn: '{{ __('Guardar configuración') }}',
                            accept_all_btn: '{{ __('Aceptar todo') }}',
                            reject_all_btn: '{{ __('Rechazar todo') }}',
                            close_btn_label: '{{ __('Cerrar') }}',
                            cookie_table_headers: [{
                                col1: '{{ __('Nombre') }}'
                            },
                            {
                                col2: '{{ __('Dominio') }}'
                                },
                                {
                                    col3: '{{ __('Expiración') }}'
                                },
                                {
                                    col4: '{{ __('Descripción') }}'
                                }
                            ],
                            blocks: [{
                                title: parsed.cookie_title + ' 📢',
                                description: parsed.cookie_description +'.'
                            }, {
                                title: parsed.strictly_cookie_title,
                                description: parsed.strictly_cookie_description,
                                toggle: {
                                    value: 'necessary',
                                    enabled: true,
                                    readonly: true // cookie categories with readonly=true are all treated as "necessary cookies"
                                }
                            }, {
                                title: '{{ __('Más información') }}',
                                description: '{{$setting['more_information_description']}} <a class="cc-link" href="{{$setting['contactus_url']}}">{{ __('contáctenos') }}</a>.',
                            }]
                        }
                    }
                }
                
            });
        </script>
