import { describe, it, expect } from 'vitest'
import { roleLabel } from '@/utils/roleLabel'

describe('roleLabel', () => {
    it('returns "Applicant" for applicant role', () => {
        expect(roleLabel('applicant')).toBe('Applicant')
    })

    it('returns "HRMPSB" for hrmpsb role', () => {
        expect(roleLabel('hrmpsb')).toBe('HRMPSB')
    })

    it('returns "Admin" for admin role', () => {
        expect(roleLabel('admin')).toBe('Admin')
    })

    it('falls back to the raw value for unknown roles', () => {
        expect(roleLabel('superadmin')).toBe('superadmin')
    })
})
