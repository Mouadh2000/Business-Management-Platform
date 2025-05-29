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

export const getAllTasks = async () => {
    try {
        const response = await axiosInstance.get('/tasks');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};
export const getMyTasks = async () => {
    try {
        const response = await axiosInstance.get('/tasks/my-tasks');
        return response.data;
    } catch (error) {
        showErrorAlert(error);
        return false;
    }
};

export const assignTaskToUser = async (payload) => {
    try {
        const response = await axiosInstance.post('/task/create', payload);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Task assigned successfully',
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

export const updateTaskStatus = async (taskId, statusData) => {
    try {
        const response = await axiosInstance.put(
            `/task/update-status/${taskId}`,
            statusData
        );
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Task status updated successfully',
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

export const updateAssignmentStatus = async (assignmentId, statusData) => {
    try {
        const response = await axiosInstance.put(
            `/task/assignment/${assignmentId}/status`,
            statusData
        );
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Assignment status updated successfully',
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

export const deleteTaskById = async (taskId) => {
    try {
        const response = await axiosInstance.delete(`task/delete/${taskId}`);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Task deleted successfully',
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

export const updateTaskById = async (taskId, taskData) => {
    try {
        const response = await axiosInstance.put(`task/update/${taskId}`, taskData);
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Task updated successfully',
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
