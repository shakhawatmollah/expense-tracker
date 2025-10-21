import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import ToastNotification from '@/components/common/ToastNotification.vue'

describe('ToastNotification', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('renders success toast correctly', () => {
    const wrapper = mount(ToastNotification, {
      props: {
        show: true,
        type: 'success',
        title: 'Success!',
        message: 'Operation completed successfully'
      },
      global: {
        plugins: [createPinia()]
      }
    })

    expect(wrapper.text()).toContain('Success!')
    expect(wrapper.text()).toContain('Operation completed successfully')
    expect(wrapper.classes()).toContain('bg-green-50')
  })

  it('renders error toast correctly', () => {
    const wrapper = mount(ToastNotification, {
      props: {
        show: true,
        type: 'error',
        title: 'Error!',
        message: 'Something went wrong'
      },
      global: {
        plugins: [createPinia()]
      }
    })

    expect(wrapper.text()).toContain('Error!')
    expect(wrapper.text()).toContain('Something went wrong')
    expect(wrapper.classes()).toContain('bg-red-50')
  })

  it('does not render when show is false', () => {
    const wrapper = mount(ToastNotification, {
      props: {
        show: false,
        type: 'success',
        title: 'Success!',
        message: 'Hidden toast'
      },
      global: {
        plugins: [createPinia()]
      }
    })

    expect(wrapper.text()).toBe('')
  })
})
