import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import StatusBadge from '@/Components/UI/StatusBadge.vue'

describe('StatusBadge', () => {
    it('renders the status label', () => {
        const wrapper = mount(StatusBadge, { props: { status: 'submitted' } })
        expect(wrapper.text()).toBe('Submitted')
    })

    it('renders fallback label for unknown status', () => {
        const wrapper = mount(StatusBadge, { props: { status: 'unknown_status' } })
        expect(wrapper.text()).toBe('unknown status')
    })

    it('has role="status"', () => {
        const wrapper = mount(StatusBadge, { props: { status: 'draft' } })
        expect(wrapper.attributes('role')).toBe('status')
    })

    it('applies correct badge class for draft', () => {
        const wrapper = mount(StatusBadge, { props: { status: 'draft' } })
        expect(wrapper.classes()).toContain('bg-gray-100')
        expect(wrapper.classes()).toContain('text-gray-600')
    })
})
