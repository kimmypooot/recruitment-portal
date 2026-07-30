import { describe, it, expect } from 'vitest'
import { formatName } from '@/utils/formatName'

describe('formatName', () => {
    it('returns empty string for null', () => {
        expect(formatName(null)).toBe('')
    })

    it('returns empty string for undefined', () => {
        expect(formatName(undefined)).toBe('')
    })

    it('formats name with middle name', () => {
        expect(formatName({ last_name: 'Doe', first_name: 'John', middle_name: 'Michael' }))
            .toBe('Doe, John M.')
    })

    it('formats name without middle name', () => {
        expect(formatName({ last_name: 'Doe', first_name: 'Jane', middle_name: '' }))
            .toBe('Doe, Jane')
    })

    it('handles null middle name', () => {
        expect(formatName({ last_name: 'Smith', first_name: 'Adam', middle_name: null }))
            .toBe('Smith, Adam')
    })
})
