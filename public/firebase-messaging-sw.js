importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js');
firebase.initializeApp({apiKey: "AIzaSyArVOMOX8L3YNtNwQYYLNu4IfsYDUUAFfg",authDomain: "rb00-1948e.firebaseapp.com",projectId: "rb00-1948e",storageBucket: "rb00-1948e.firebasestorage.app", messagingSenderId: "973551472641", appId: "1:973551472641:web:fcce7958472337860f3000"});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) { return self.registration.showNotification(payload.data.title, { body: payload.data.body ? payload.data.body : '', icon: payload.data.icon ? payload.data.icon : '' }); });
