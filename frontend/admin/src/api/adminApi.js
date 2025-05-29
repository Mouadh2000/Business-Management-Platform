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

export const getAllUsers = async () => {
    try {
        const response = await axiosInstance.get('');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const getAllStaffUsers = async (projectId) => {
    try {
        const response = await axiosInstance.get(`/staffs/${projectId}`);
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const deleteUserById = async (userId) => {
    try {
        const response = await axiosInstance.delete(`/delete/${userId}`);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'User deleted successfully',
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

export const updateUserById = async (userId, userData) => {
    try {
        const response = await axiosInstance.put(`/update/${userId}`, userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'User updated successfully',
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

export const addUser = async (userData) => {
    try {
        const response = await axiosInstance.post('/create', userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'User added successfully',
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