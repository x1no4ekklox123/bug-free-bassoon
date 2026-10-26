import React from 'react';
import Icon from '@/components/elements/Icon';
import { IconDefinition } from '@fortawesome/free-solid-svg-icons';
import classNames from 'classnames';
import styles from './style.module.css';
import useFitText from 'use-fit-text';
import CopyOnClick from '@/components/elements/CopyOnClick';

interface StatBlockProps {
    title: string;
    copyOnClick?: string;
    color?: string | undefined;
    icon: IconDefinition;
    children: React.ReactNode;
    className?: string;
}

const DEFAULT_COLOR = 'bg-primary-500/20';

// Alert colors passed in from the parent are rendered as soft tinted badges rather than solid blocks.
const ICON_TONES: Record<string, string> = {
    'bg-red-500': 'bg-red-500/20 text-red-300 ring-1 ring-red-500/50',
    'bg-yellow-500': 'bg-yellow-500/20 text-yellow-300 ring-1 ring-yellow-500/50',
};

export default ({ title, copyOnClick, icon, color, className, children }: StatBlockProps) => {
    const { fontSize, ref } = useFitText({ minFontSize: 8, maxFontSize: 500 });

    return (
        <CopyOnClick text={copyOnClick}>
            <div className={classNames(styles.stat_block, className)}>
                <div className={classNames(styles.status_bar, color || DEFAULT_COLOR)} />
                <div
                    className={classNames(
                        styles.icon,
                        color ? ICON_TONES[color] || color : DEFAULT_COLOR,
                        { [styles.icon_default]: !color }
                    )}
                >
                    <Icon icon={icon} className={classNames({ 'text-primary-300': !color, 'text-current': !!color })} />
                </div>
                <div className={'flex flex-col justify-center overflow-hidden w-full'}>
                    <p className={'font-header font-medium leading-tight text-xs md:text-sm text-gray-200'}>{title}</p>
                    <div
                        ref={ref}
                        className={'h-[1.75rem] w-full font-semibold text-gray-50 truncate'}
                        style={{ fontSize }}
                    >
                        {children}
                    </div>
                </div>
            </div>
        </CopyOnClick>
    );
};
