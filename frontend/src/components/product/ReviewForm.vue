<script setup lang="ts">
import { ref } from 'vue';
import { Star } from 'lucide-vue-next';

const props = defineProps<{
  productId: number;
  initialRating?: number;
  initialComment?: string;
  isEditing?: boolean;
}>();

const emit = defineEmits<{
  submit: [rating: number, comment: string];
  cancel: [];
}>();

const rating = ref(props.initialRating || 5);
const comment = ref(props.initialComment || '');
const hoverRating = ref(0);

const submit = () => {
  if (!rating.value) return;
  emit('submit', rating.value, comment.value);
};
</script>

<template>
  <form @submit.prevent="submit" class="review-form">
    <div class="star-selector">
      <span class="star-label">Rating</span>
      <div class="stars">
        <button
          v-for="i in 5"
          :key="i"
          type="button"
          class="star-btn"
          @click="rating = i"
          @mouseenter="hoverRating = i"
          @mouseleave="hoverRating = 0"
        >
          <Star
            :size="22"
            :class="[
              'star-icon',
              { filled: i <= (hoverRating || rating) }
            ]"
          />
        </button>
      </div>
    </div>

    <div class="field">
      <label for="review-comment">Comment <span class="optional">(optional)</span></label>
      <textarea
        id="review-comment"
        v-model="comment"
        class="form-input"
        rows="3"
        placeholder="Share your experience with this product..."
      />
    </div>

    <div class="actions">
      <button v-if="isEditing" type="button" class="btn btn-secondary btn-sm" @click="emit('cancel')">
        Cancel
      </button>
      <button type="submit" class="btn btn-primary btn-sm">
        {{ isEditing ? 'Update Review' : 'Submit Review' }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.review-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: var(--radius-md);
}

.star-selector {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.star-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary, #9ca3af);
}

.stars {
  display: flex;
  gap: 4px;
}

.star-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  color: #374151;
  transition: color var(--transition-fast);
}

.star-btn:hover .star-icon {
  color: #fbbf24;
}

.star-icon.filled {
  color: #fbbf24;
  fill: #fbbf24;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-secondary, #9ca3af);
}

.optional {
  font-weight: 400;
  color: var(--text-tertiary, #6b7280);
  font-size: 0.8rem;
}

.actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}
</style>
