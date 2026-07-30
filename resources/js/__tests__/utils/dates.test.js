import { describe, it, expect } from 'vitest'
import { formatDate, formatDateLong, formatDateTime, formatDateRange, daysRemaining, isPastDeadline, deadlineUrgency, timeAgo } from '@/utils/dates'

describe('formatDate', () => {
    it('returns — for null', () => {
        expect(formatDate(null)).toBe('—')
    })

    it('formats a date string', () => {
        const result = formatDate('2026-07-01')
        expect(result).toContain('Jul')
        expect(result).toContain('1')
        expect(result).toContain('2026')
    })
})

describe('formatDateLong', () => {
    it('returns — for null', () => {
        expect(formatDateLong(null)).toBe('—')
    })

    it('formats a date long', () => {
        const result = formatDateLong('2026-07-01')
        expect(result).toContain('July')
    })
})

describe('formatDateTime', () => {
    it('returns — for null', () => {
        expect(formatDateTime(null)).toBe('—')
    })
})

describe('formatDateRange', () => {
    it('returns — for no start', () => {
        expect(formatDateRange(null)).toBe('—')
    })

    it('renders present range', () => {
        const result = formatDateRange('2020-06-01', null, true)
        expect(result).toContain('Present')
    })

    it('renders single day', () => {
        const result = formatDateRange('2026-07-01', '2026-07-01')
        expect(result).toContain('Jul. 1, 2026')
    })
})

describe('daysRemaining', () => {
    it('returns null for null', () => {
        expect(daysRemaining(null)).toBeNull()
    })
})

describe('isPastDeadline', () => {
    it('returns false for null', () => {
        expect(isPastDeadline(null)).toBe(false)
    })
})

describe('deadlineUrgency', () => {
    it('returns none for no date', () => {
        const result = deadlineUrgency(null)
        expect(result.level).toBe('none')
    })
})

describe('timeAgo', () => {
    it('returns empty for null', () => {
        expect(timeAgo(null)).toBe('')
    })
})
