import Vue from 'vue';
import VueCookies from 'vue-cookies';
const eventHub = new Vue();

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

        this.eventHub.$on('globalLoading', (val) => {
            this.globalLoading = val;
        });

    },

    data : function(){
        return {
            cookieKey : 'ohmscookiekey',
            apiUrl : 'http://122.53.152.8/ohms/api',
            // apiUrl : 'http://localhost/ohms/api',
            websocket : 'ws://122.53.152.8:3552/',
            token : '',
            eventHub: eventHub,
            userData : {},
            gender: ["Male", "Female"],
            formRules: {
                numberRequired: value => !(value  == 0) || 'Should be more than zero',
                required: value => !!value || 'Required',
                email: value => {
                    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    return pattern.test(value) || 'Invalid e-mail.'
                },
                phoneNumber : value => {
                    const pattern = /^(09|\+639)\d{9}$/g
                    return pattern.test(value) || 'Should be 09xxxxxxxxx/+639xxxxxxxxx';
                },
                requiredGreterThanZero : value => {
                    const pattern = /^[1-9]\d*$/
                    return pattern.test(value) || 'Required';
                },
                textOnly : value => {
                    const pattern = (/^[A-Za-z ]+$/)
                    return pattern.test(value) || 'Please use letters only';  
                },
                licenseNumber : value => {
                    const pattern = /^[0-9]\d{6}$/g
                    return pattern.test(value) || 'Should be 7 digit number';
                }
            },
            globalLoading : false,
            wsconnect : ''
        }
    }, 
    methods : {
        getBirthAge : function(dateofbirth){
            var birthDate = new Date(dateofbirth);
            var currentDate = new Date();
            var age = 0;
            var month = currentDate.getMonth() >= birthDate.getMonth();
            var date = currentDate.getDate() >= birthDate.getDate();
                
            if(currentDate > birthDate){
                age = currentDate.getYear() - birthDate.getYear() -1;
                if(month){
                    if(date){
                        age = currentDate.getYear() - birthDate.getYear();
                    }
                }
            }else{
                if( !(((currentDate.getYear() - birthDate.getYear()) -1) < 0) ){
                    age = (currentDate.getYear() - birthDate.getYear()) -1;
                }
            }
            return age;
        },
        zeroPad : function(num, numZeros) {
            var currentYear = new Date().toISOString().substr(2,2);
            var n = Math.abs(num);
            var zeros = Math.max(0, numZeros - Math.floor(n).toString().length );
            var zeroString = Math.pow(10,zeros).toString().substr(1);
            if( num < 0 ) {
                zeroString = '-' + zeroString;
            }
            return currentYear+zeroString+n;
        },
        timePad : function(num, numZeros){
            var n = Math.abs(num);
            var zeros = Math.max(0, numZeros - Math.floor(n).toString().length);
            var zeroString = Math.pow(10,zeros).toString().substr(1);
            return zeroString+n;
        },
        dateFormat : function(date){
            let d = new Date(date);
            return d.toDateString().substr(4);
        },
        changeLicenseFormat: function(id = ''){
            var idFormat = id.substr(0,2)+"-"+id.substr(2,7);
            return idFormat;
        }
    }
};
