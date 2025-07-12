import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Add request interceptor to include Bearer token
window.axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem("access_token");
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
            console.log(
                "Adding Authorization header:",
                `Bearer ${token.substring(0, 20)}...`
            );
        } else {
            console.log("No access token found in localStorage");
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add response interceptor to handle 401 errors
window.axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        console.log(
            "Response error:",
            error.response?.status,
            error.response?.data
        );
        if (error.response && error.response.status === 401) {
            console.log("401 Unauthorized - redirecting to login");
            // Token is invalid or expired, redirect to login
            localStorage.removeItem("access_token");
            localStorage.removeItem("user");
            window.location.href = "/login";
        }
        return Promise.reject(error);
    }
);
