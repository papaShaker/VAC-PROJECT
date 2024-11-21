<script setup>
import { defineProps, defineEmits, ref } from "vue";

const props = defineProps({
  isOpen: Boolean,
});

const emit = defineEmits(["modal-close"]);

/* const target = ref(null); */

</script>

<template>
  <div v-if="isOpen" class="modal-mask">
    <div class="modal-wrapper">
      <div class="modal-container" ref="target"><!--  -->
        <div class="bg-gray-600 p-3 rounded-md"><!-- inside body -->
          <div class="modal-header">
            <slot name="modal-header"> ... </slot>
          </div>
          <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">
          <div class="modal-content">
            <slot name="modal-content"> ... </slot>
          </div>
          <hr class="h-px mt-3 mb-0 bg-gray-200 border-0 dark:bg-gray-700">
          <div class="modal-footer">
            <slot name="modal-footer">
              <div class="">
                <button @click.stop="emit('modal-close')"
                  class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">Cerrar</button>
              </div>
            </slot>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-mask {
  position: fixed;
  z-index: 9998;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(56, 56, 78, 0.342);
}
.modal-container {
  width: 300px;
  margin: 150px auto;
  padding: 6px 10px;
  background-color: #192938;
  color: #FFFF;
  border-radius: 5px;
  box-shadow: 0 2px 8px rgb(0, 0, 0);
}

</style>