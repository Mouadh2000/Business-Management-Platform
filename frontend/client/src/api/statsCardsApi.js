import axiosInstance from './axiosApi';

export const getCountEmployees = async () => {
    try {
        const response = await axiosInstance.get('/count-employees');
        return response.data.data;
    } catch (error) {
        return false;
    }
};

export const getCountClients = async () => {
    try {
        const response = await axiosInstance.get('/count-clients');
        return response.data.data;
    } catch (error) {
        return false;
    }
}

export const getCountProjects = async () => {
    try {
        const response = await axiosInstance.get('/count-projects');
        return response.data.data;
    } catch (error) {
        return false;
    }
}

export const getCountTasks = async () => {
    try {
        const response = await axiosInstance.get('/count-tasks');
        return response.data.data;
    } catch (error) {
        return false;
    }
}

export const getProjectCountByMonth = async () => {
    try {
        const response = await axiosInstance.get('/project-by-month');
        return response.data;
    } catch (error) {
        return false;
    }
}

export const getTaskCountByMonth = async () => {
    try {
        const response = await axiosInstance.get('/task-by-month');
        return response.data;
    } catch (error) {
        return false;
    }
}
