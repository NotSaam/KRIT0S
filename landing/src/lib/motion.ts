import type { Transition, Variants } from "framer-motion";

/** Easing firma compartida con el portfolio: settle suave tipo out-expo. */
export const EASE_SIGNATURE = [0.16, 1, 0.3, 1] as const;

export const T_SIGNATURE: Transition = { duration: 0.7, ease: EASE_SIGNATURE };
export const T_QUICK: Transition = { duration: 0.45, ease: EASE_SIGNATURE };
export const SPRING_SNAPPY: Transition = { type: "spring", stiffness: 380, damping: 30 };

export const revealVariants: Variants = {
  hidden: { opacity: 0, y: 26 },
  show: { opacity: 1, y: 0, transition: T_SIGNATURE },
};

export const staggerContainer: Variants = {
  hidden: {},
  show: { transition: { staggerChildren: 0.09, delayChildren: 0.06 } },
};

export const staggerItem: Variants = {
  hidden: { opacity: 0, y: 20 },
  show: { opacity: 1, y: 0, transition: T_SIGNATURE },
};
