import axiosInstance from './axiosApi';
import Swal from 'sweetalert2';

const showErrorAlert = (error) => {
    let title = 'Error';
    let text = 'An unexpected error occurred';
    
    if (error.response) {
        const { status, data } = error.response;
        
        if (status === 403) {
            title = 'Permission Denied';
            text = 'You do not have permission to perform this action';
        } else if (status === 404) {
            title = 'Not Found';
            text = 'The requested resource was not found';
        } else if (status === 422 && data.errors) {
            title = 'Validation Error';
            text = Object.values(data.errors)
                .map(err => Array.isArray(err) ? err[0] : err)
                .join('\n');
        } else if (data.message) {
            text = data.message;
        }
    } else if (error.request) {
        text = 'No response received from server';
    } else {
        text = error.message || text;
    }
    
    Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        customClass: {
            container: 'custom-swal-container'
        }
    });
};

export const getAllProjects = async () => {
    try {
        const response = await axiosInstance.get('projects');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const getProjectWithInfo = async () => {
    try {
        const response = await axiosInstance.get('project-info');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const getBillByProjectId = async (projectId) => {
    try {
        const response = await axiosInstance.get(`project/bill/${projectId}`);
        return response.data;
    } catch (error) {
        throw error;
    }
};

export const deleteProjectById = async (userId) => {
    try {
        const response = await axiosInstance.delete(`project/delete/${userId}`);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Project deleted successfully',
            customClass: {
                container: 'custom-swal-container'
            }
        });
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        throw error;
    }
};

export const updateProjectById = async (userId, userData) => {
    try {
        const response = await axiosInstance.put(`project/update/${userId}`, userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Project updated successfully',
            customClass: {
                container: 'custom-swal-container'
            }
        });
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        throw error;
    }
};

export const addProject = async (userData) => {
    try {
        const response = await axiosInstance.post('project/create', userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Project added successfully',
            customClass: {
                container: 'custom-swal-container'
            }
        });
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        throw error;
    }
};

export const assignStaffToProject = async (projectId, staffData) => {
    try {
        const response = await axiosInstance.post(`project/affect-employee/${projectId}`, staffData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Staff assigned to project successfully',
            customClass: {
                container: 'custom-swal-container'
            }
        });
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        throw error;
    }
};