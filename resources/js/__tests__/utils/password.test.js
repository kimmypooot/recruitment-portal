import { describe, it, expect } from 'vitest'
import { passwordRequirements, unmetPasswordRequirements } from '@/utils/password'

describe('passwordRequirements', () => {
    it('returns all unmet for empty password', () => {
        const results = passwordRequirements('')
        expect(results.every(r => !r.met)).toBe(true)
        expect(results).toHaveLength(4)
    })

    it('returns all met for strong password', () => {
        const results = passwordRequirements('Abcdef1g')
        expect(results.every(r => r.met)).toBe(true)
    })

    it('detects missing uppercase', () => {
        const results = passwordRequirements('abcdef1g')
        expect(results[1].met).toBe(false)
    })

    it('detects missing lowercase', () => {
        const results = passwordRequirements('ABCDEF1G')
        expect(results[2].met).toBe(false)
    })

    it('detects missing number', () => {
        const results = passwordRequirements('Abcdefgh')
        expect(results[3].met).toBe(false)
    })

    it('detects short password', () => {
        const results = passwordRequirements('Ab1')
        expect(results[0].met).toBe(false)
    })
})

describe('unmetPasswordRequirements', () => {
    it('lists unmet requirements (strips "At least " prefix)', () => {
        const unmet = unmetPasswordRequirements('')
        expect(unmet).toContain('8 characters')
        expect(unmet).toContain('one uppercase letter')
        expect(unmet).toContain('one lowercase letter')
        expect(unmet).toContain('one number')
    })

    it('returns empty for strong password', () => {
        expect(unmetPasswordRequirements('StrongPass1')).toEqual([])
    })
})
