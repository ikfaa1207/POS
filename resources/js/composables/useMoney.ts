import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const isNumberPart = (type: string) =>
    type === 'integer' ||
    type === 'group' ||
    type === 'decimal' ||
    type === 'fraction';

export const useMoney = () => {
    const page = usePage();
    const currency = computed(() => (page.props as { currency?: string }).currency ?? 'USD');
    const thinSpace = '\u2009';

    const formatMoney = (value: string | number) =>
        new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency.value,
        })
            .formatToParts(Number(value))
            .reduce((output, part, index, parts) => {
                if (part.type === 'currency') {
                    const next = parts[index + 1];
                    const nextNext = parts[index + 2];
                    output += part.value;

                    if (next?.type === 'literal' && next.value.trim() === '' && nextNext && isNumberPart(nextNext.type)) {
                        return `${output}${thinSpace}`;
                    }

                    if (next && isNumberPart(next.type)) {
                        return `${output}${thinSpace}`;
                    }

                    return output;
                }

                if (part.type === 'literal' && part.value.trim() === '') {
                    const prev = parts[index - 1];
                    const next = parts[index + 1];
                    if (
                        (prev && isNumberPart(prev.type) && next?.type === 'currency') ||
                        (prev?.type === 'currency' && next && isNumberPart(next.type))
                    ) {
                        return `${output}${thinSpace}`;
                    }
                }

                return output + part.value;
            }, '');

    return { currency, formatMoney };
};
