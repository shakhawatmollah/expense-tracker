import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useNotificationsStore } from '@/stores/notifications'

describe('ToastNotification', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should add success notification to store', () => {
    const store = useNotificationsStore()
    
    store.notifySuccess('Success!', 'Operation completed successfully')
    
    expect(store.notifications).toHaveLength(1)
    expect(store.notifications[0].type).toBe('success')
    expect(store.notifications[0].title).toBe('Success!')
    expect(store.notifications[0].message).toBe('Operation completed successfully')
  })

  it('should add error notification to store', () => {
    const store = useNotificationsStore()
    
    store.notifyError('Error!', 'Something went wrong')
    
    expect(store.notifications).toHaveLength(1)
    expect(store.notifications[0].type).toBe('error')
    expect(store.notifications[0].title).toBe('Error!')
    expect(store.notifications[0].message).toBe('Something went wrong')
  })

  it('should dismiss notification', () => {
    const store = useNotificationsStore()
    
    store.notifySuccess('Test', 'Test message')
    const notificationId = store.notifications[0].id
    
    expect(store.notifications).toHaveLength(1)
    
    store.dismissNotification(notificationId)
    
    expect(store.activeNotifications).toHaveLength(0)
  })
})

