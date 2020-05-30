import Vue from 'vue';
import VueCookies from 'vue-cookies';
const eventHub = new Vue();
import moment from 'moment-timezone';

moment.tz.setDefault('Asia/Manila');

export default {

    watch : {

    },

    mounted : function(){
        let cookie = VueCookies.get(this.cookieKey);
        if(cookie){
            this.token = cookie.token;
            this.userData = cookie.data;
        }
    },

    created : function(){
        
    },

    data : function(){
        return {
            cookieKey : 'ohmscookiekey',
            apiUrl : 'http://122.53.152.8/ohms/api',
            // apiUrl : 'http://localhost/ohms/api',
            token : '',
            eventHub: eventHub,
            userData : {},
            gender: ["Male", "Female"],
            formRules: {
                numberRequired: value => !(value  == 0) || 'Should be more than zero',
                required: value => !!value || 'Required',
            },
            globalLoading : false,
            wsconnect : '',
            today : moment().format('YYYY-MM-DD'),
        }
    }, 
    methods : {
    
    }
};
