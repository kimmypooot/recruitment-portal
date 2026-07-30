import { describe, it, expect } from 'vitest'
import {
    statusLabel,
    statusBadgeClass,
    statusIcon,
    statusBorderClass,
    statusChipClass,
    pipelineStep,
    isPipelinePast,
    isTerminal,
    canWithdraw,
} from '@/config/statusConfig'

describe('statusLabel', () => {
    it('returns "Submitted" for submitted', () => {
        expect(statusLabel('submitted')).toBe('Submitted')
    })

    it('falls back to replacing underscores', () => {
        expect(statusLabel('custom_status')).toBe('custom status')
    })
})

describe('statusBadgeClass', () => {
    it('returns class for known status', () => {
        expect(statusBadgeClass('submitted')).toContain('text-primary')
    })

    it('returns default for unknown status', () => {
        expect(statusBadgeClass('unknown')).toBe('bg-gray-100 text-gray-600')
    })
})

describe('statusIcon', () => {
    it('returns icon for known status', () => {
        expect(statusIcon('submitted').bg).toContain('yellow')
    })

    it('returns default for unknown', () => {
        expect(statusIcon('unknown').bg).toBe('bg-gray-50')
    })
})

describe('statusBorderClass', () => {
    it('returns class for known status', () => {
        expect(statusBorderClass('submitted')).toBe('border-l-amber-400')
    })
})

describe('statusChipClass', () => {
    it('returns class for known status', () => {
        expect(statusChipClass('submitted')).toContain('yellow')
    })
})

describe('pipelineStep', () => {
    it('returns number for a pipeline status', () => {
        expect(pipelineStep('submitted')).toBe(1)
        expect(pipelineStep('exam_scheduled')).toBe(5)
    })

    it('returns ? for non-pipeline status', () => {
        expect(pipelineStep('unknown')).toBe('?')
    })
})

describe('isPipelinePast', () => {
    it('returns true for upstream status', () => {
        expect(isPipelinePast('submitted', 'screened')).toBe(true)
    })

    it('returns false for downstream status', () => {
        expect(isPipelinePast('screened', 'submitted')).toBe(false)
    })
})

describe('isTerminal', () => {
    it('returns true for terminal statuses', () => {
        expect(isTerminal('appointed')).toBe(true)
        expect(isTerminal('withdrawn')).toBe(true)
    })

    it('returns false for non-terminal', () => {
        expect(isTerminal('submitted')).toBe(false)
    })
})

describe('canWithdraw', () => {
    it('allows withdrawal for early statuses', () => {
        expect(canWithdraw('submitted')).toBe(true)
        expect(canWithdraw('under_review')).toBe(true)
    })

    it('disallows withdrawal for later statuses', () => {
        expect(canWithdraw('interviewed')).toBe(false)
        expect(canWithdraw('appointed')).toBe(false)
    })
})
