import axios from 'axios';

const baseURL = 'http://127.0.0.1:8000/api/admin';
const axiosInstance = axios.create({
    baseURL: baseURL,
    timeout: 5000,
    headers: {
        'Content-Type': 'application/json',
        'accept': 'application/json'
    }
});


axiosInstance.interceptors.request.use((config) => {
    const token = localStorage.getItem('access_token');
    if (token) {
        config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

axiosInstance.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;

        if (error.response.status === 401 && !originalRequest._retry) {
            if (originalRequest.url === baseURL + 'login/refresh/') {
                localStorage.clear();
                window.location.href = '/login';
                return Promise.reject(error);
            }

            if (error.response.data.code === "token_not_valid") {
                const refreshToken = localStorage.getItem('refresh_token');
                if (refreshToken) {
                    const tokenParts = JSON.parse(atob(refreshToken.split('.')[1]));
                    const now = Math.ceil(Date.now() / 1000);

                    if (tokenParts.exp > now) {
                        originalRequest._retry = true;

                        try {
                            const response = await axiosInstance.post('login/refresh/', { refresh: refreshToken });
                            localStorage.setItem('access_token', response.data.access);
                            localStorage.setItem('refresh_token', response.data.refresh);
                            axiosInstance.defaults.headers['Authorization'] = "Bearer " + response.data.access;
                            originalRequest.headers['Authorization'] = "Bearer " + response.data.access;
                            return axiosInstance(originalRequest);
                        } catch (err) {
                            console.log(err);
                            localStorage.clear();
                            window.location.href = '/login';
                            return Promise.reject(err);
                        }
                    } else {
                        console.log("Refresh token is expired", tokenParts.exp, now);
                        localStorage.clear();
                        window.location.href = '/login';
                    }
                } else {
                    console.log("Refresh token not available.");
                    localStorage.clear();
                    window.location.href = '/login';
                }
            }
        }

        return Promise.reject(error);
    }
);

export default axiosInstance;