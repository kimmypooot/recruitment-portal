<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EoptResult extends Model
{
    protected $fillable = [
        'application_id',
        'emotional_stability',
        'extraversion',
        'openness_to_experience',
        'agreeableness',
        'conscientiousness',
        'remarks',
    ];

    const CATEGORIES = [
        'emotional_stability',
        'extraversion',
        'openness_to_experience',
        'agreeableness',
        'conscientiousness',
    ];

    const RATINGS = ['very_high', 'high', 'average', 'low', 'very_low'];

    public static function ratingLabel(string $rating): string
    {
        return match ($rating) {
            'very_high' => 'Very High',
            'high' => 'High',
            'average' => 'Average',
            'low' => 'Low',
            'very_low' => 'Very Low',
            default => ucfirst(str_replace('_', ' ', $rating)),
        };
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            'emotional_stability' => 'Emotional Stability',
            'extraversion' => 'Extraversion',
            'openness_to_experience' => 'Openness to Experience',
            'agreeableness' => 'Agreeableness',
            'conscientiousness' => 'Conscientiousness',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    public static function getDefinition(string $category, string $rating): string
    {
        $group = in_array($rating, ['very_high', 'high']) ? 'high' : (in_array($rating, ['low', 'very_low']) ? 'low' : $rating);

        return self::DEFINITIONS[$category][$group] ?? '';
    }

    public static function getAllDefinitions(): array
    {
        $result = [];
        foreach (self::CATEGORIES as $cat) {
            foreach (self::RATINGS as $rating) {
                $result[$cat][$rating] = self::getDefinition($cat, $rating);
            }
        }

        return $result;
    }

    const DEFINITIONS = [
        'emotional_stability' => [
            'high' => 'Has a tendency to be calm, relaxed, unemotional, hardy, secure and free from psychological distress and maladaptive coping responses',
            'average' => 'Generally secure, free from worry, and capable of handling stressful situations, but may at times be oversensitive and pre-occupied, and feel overwhelmed by problems',
            'low' => 'Tends to be easily worried and upset, oversensitive, and overwhelmed when faced with problems',
        ],
        'extraversion' => [
            'high' => 'Characterized by a high activity level, capacity for joy, and need for stimulation; tendency to be sociable, talkative, person-oriented, optimistic',
            'average' => 'Occasionally sociable, active, and talkative; but may also be reserved, unexuberant, retiring, and quiet',
            'low' => 'Tends to be reserved, sober, unexuberant, retiring, and quiet',
        ],
        'openness_to_experience' => [
            'high' => 'Has a tendency to be intellectually curious, creative, original, imaginative, untraditional, to have broad and artistic interests',
            'average' => 'Has some intellectual curiosity and interest in different social and religious beliefs, but does not venture too far from the conventional',
            'low' => 'Tends to be conventional, down-to-earth, unartistic, unanalytical; few interests',
        ],
        'agreeableness' => [
            'high' => 'Careful in showing basic respect for others, good-natured, understanding, sincere, and trustworthy',
            'average' => 'Generally behaves to promote harmonious relationships, but may occasionally be self-promoting, judgmental, and lacking in tact',
            'low' => 'Tends to be self-promoting, adversarial, unconcerned for others\' feelings, cynical, untrustworthy',
        ],
        'conscientiousness' => [
            'high' => 'Pronounced sense of responsibility, commitment, service, self-discipline and order',
            'average' => 'Generally responsible, helpful, self-disciplined, and orderly, but may lack consistency in these traits',
            'low' => 'Tends to be unreliable, disorganized, and lacking in helpfulness',
        ],
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
