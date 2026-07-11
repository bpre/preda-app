<?php

return [

    'forms' => [

        'heading' => 'Nowy widok',
        'name' => 'Nazwa',
        'user' => 'Właściciel',
        'resource' => 'Resource',
        'note' => 'Note',

        'status' => [

            'label' => 'Status',

        ],

        'name' => [

            'label' => 'Nazwa',
            'helper_text' => 'Wybierz krótką nazwę, dobrze opisującą widok',

        ],

        'filters' => [

            'label' => 'Zobacz podsumowanie',
            'helper_text' => 'Ta konfiguracja zostanie zapisane z tym widokiem',

        ],

        'panels' => [

            'label' => 'Panele',

        ],

        'preset_view' => [

            'label' => 'Preset view',
            'query_label' => 'Preset view query',
            'helper_text_start' => 'You are using the preset view ',
            'helper_text_end' => ' as the base for this view. Preset views may have their own independent configuration in addition to the configurations you have selected.',

        ],

        'icon' => [

            'label' => 'Ikona',
            'placeholder' => 'Wybierz ikonę',

        ],

        'color' => [

            'label' => 'Kolor',

        ],

        'public' => [

            'label' => 'Udostępnij publicznie',
            'toggle_label' => 'Pobliczny',
            'helper_text' => 'Udostępnij ten widok wszystkim użytkownikom',

        ],

        'favorite' => [

            'label' => 'Dodaj do ulubionych',
            'toggle_label' => 'Ulubiony',
            'helper_text' => 'Dodaj ten widok do ulubionych',

        ],

        'global_favorite' => [

            'label' => 'Dodaj do globalnych ulubionych',
            'toggle_label' => 'Globalnie ulubiony',
            'helper_text' => 'Dodaj ten widok do globalnie ulubionych',

        ],

    ],

    'notifications' => [

        'preset_views' => [

            'title' => 'Nie można było utworzyć widoku',
            'body' => 'New views cannot be created from a preset view. Please build your view using the Default view or any user-created view.',

        ],

        'save_view' => [

            'saved' => [

                'title' => 'Zapisano',

            ],

        ],

        'edit_view' => [

            'saved' => [

                'title' => 'Zapisano zmiany',

            ],

        ],

        'replace_view' => [

            'replaced' => [

                'title' => 'Zastąpiono',

            ],

        ],

    ],

    'quick_save' => [

        'save' => [

            'modal_heading' => 'Zapisz widok',
            'submit_label' => 'Zapisz widok',

        ],

    ],

    'select' => [

        'label' => 'Widoki',
        'placeholder' => 'Wybierz widok',

    ],

    'status' => [

        'approved' => 'zatwierdzono',
        'pending' => 'oczekuje',
        'rejected' => 'odrzucono',

    ],

    'tables' => [

        'favorites' => [

            'default' => 'Domyślny',

        ],

        'columns' => [

            'user' => 'Właściciel',
            'icon' => 'Ikona',
            'color' => 'Kolor',
            'name' => 'Nazwa widoku',
            'panel' => 'Panel',
            'resource' => 'Resource',
            'status' => 'Status',
            'filters' => 'Filtry',
            'is_public' => 'Publiczny',
            'is_user_favorite' => 'Ulubiony',
            'is_global_favorite' => 'Globalny',
            'sort_order' => 'Sortowanie',
            'users_favorite_sort_order' => 'Ulubiona kolejność sortowania',

        ],

        'tooltips' => [

            'is_user_favorite' => [

                'unfavorite' => 'Usuń z ulubionych',
                'favorite' => 'Dodaj do ulubionych',

            ],

            'is_public' => [

                'make_private' => 'Uczyń prywatnym',
                'make_public' => 'Uczyń publicznym',

            ],

            'is_global_favorite' => [

                'make_personal' => 'Uczyń osobistym',
                'make_global' => 'Uczyń globalnym',

            ],

        ],

        'actions' => [

            'buttons' => [

                'open' => 'Otwórz',
                'approve' => 'Zatwierdź',

            ],

        ],

    ],

    'toggled_columns' => [

        'visible' => 'Widoczny',
        'hidden' => 'Ukryty',

    ],

    'user_view_resource' => [

        'model_label' => 'Widok użytkownika',
        'plural_model_label' => 'Widoki użytkownika',
        'navigation_label' => 'Widoki użytkownika',

    ],

    'view_manager' => [

        'actions' => [

            'add_view_to_favorites' => 'Add to favorites',
            'apply_view' => 'Apply view',
            'save' => 'Save',
            'save_view' => 'Save view',
            'delete_view' => 'Delete view',
            'delete_view_description' => 'This view is a :type view. Other users will lose access to your view. Are you sure you would like to proceed?',
            'delete_view_modal_submit_label' => 'Delete',
            'remove_view_from_favorites' => 'Remove from favorites',
            'edit_view' => 'Edit view',
            'replace_view' => 'Replace view',
            'replace_view_modal_description' => 'You are about to replace this saved view with the table\'s current configuration. Are you sure you would like to do this?',
            'replace_view_modal_submit_label' => 'Replace',
            'show_view_manager' => 'Show view manager',

        ],

        'badges' => [

            'active' => 'active',
            'preset' => 'preset',
            'user' => 'user',
            'global' => 'global',
            'public' => 'public',

        ],

        'heading' => 'View manager',

        'table_heading' => 'Widoki',

        'no_views' => 'Brak widoków',

        'subheadings' => [

            'user_favorites' => 'User favorites',
            'user_views' => 'User views',
            'preset_views' => 'Preset views',
            'global_views' => 'Global views',
            'public_views' => 'Public views',

        ],

    ],
];
