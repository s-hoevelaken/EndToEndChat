import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'mainBackgroundColor': '#27292D',
                'hoverFriendsListColor': '#2C2E33',
                'customBlue': '#3A84F7',
                'separatorBlue': '#323336',
                'lastMessageColorFriendsList': '#626664',
                'FriendNameTextColor': '#CECECF',
                'borderColorGrey': '#323336',
                'backgroundMessagesField': '#303236',
            }
        },
    },

    plugins: [forms, typography],
};
