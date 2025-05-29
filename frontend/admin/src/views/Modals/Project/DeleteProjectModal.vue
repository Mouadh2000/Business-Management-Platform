<template>
    <b-modal v-model="isOpen" title="Delete Confirmation" @hide="toggle">
        <template v-slot:default>
        <p>Are you sure you want to delete this Project?</p>
        </template>
        <template v-slot:modal-footer>
        <b-button variant="danger" @click="onDelete">Delete</b-button>
        <b-button variant="secondary" @click="toggle">Cancel</b-button>
        </template>
    </b-modal>
</template>

<script>
export default {
    props: {
        isOpen: {
        type: Boolean,
        required: true,
        },
        toggle: {
        type: Function,
        required: true,
        },
        onDelete: {
        type: Function,
        required: true,
        },
    },
};
</script>
  

  