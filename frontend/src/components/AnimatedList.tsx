import { motion } from 'framer-motion';
import type { ReactNode } from 'react';

interface Props {
  children: ReactNode;
  index: number;
}

export function AnimatedRow({ children, index }: Props) {
  return (
    <motion.tr
      initial={{ opacity: 0, x: -8 }}
      animate={{ opacity: 1, x: 0 }}
      transition={{ duration: 0.2, delay: index * 0.03 }}
      className="hover:bg-gray-50"
    >
      {children}
    </motion.tr>
  );
}
