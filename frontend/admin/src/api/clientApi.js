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

export const getAllClients = async () => {
    try {
        const response = await axiosInstance.get('clients');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const getClientById = async (clientId) => {
    try {
        const response = await axiosInstance.get(`client/${clientId}`);
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const deleteClientById = async (userId) => {
    try {
        const response = await axiosInstance.delete(`client/delete/${userId}`);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Client deleted successfully',
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

export const updateClientById = async (userId, userData) => {
    try {
        const response = await axiosInstance.put(`client/update/${userId}`, userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Client updated successfully',
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

export const addClient = async (userData) => {
    try {
        const response = await axiosInstance.post('client/create', userData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Client added successfully',
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