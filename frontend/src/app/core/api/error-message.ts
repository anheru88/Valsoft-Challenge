import { apiError } from './api-error';

/**
 * What a refused request means, in the words the screen should use.
 *
 * The client keys on the stable code, never on the prose (API specification
 * 10): the server may reword a message or localize it, and the code is what is
 * promised not to change. Anything unknown falls back to the server's own
 * message, so a code added after this build still says something useful.
 */
const MESSAGES: Record<string, string> = {
  // Books
  BOOK_HAS_ACTIVE_LOANS: 'This book still has copies on loan. Check them back in before removing the title.',
  BOOK_COPIES_BELOW_LOANED: 'You cannot hold fewer copies than the ones currently on loan.',

  // Authors and categories
  AUTHOR_IN_USE: 'This author is attached to books in the catalogue. Reassign them first.',
  CATEGORY_IN_USE: 'This category still holds books. Recategorise them first.',

  // Circulation
  LOAN_NO_COPIES: 'No copies of this book are available right now.',
  LOAN_LIMIT_REACHED: 'This member has already reached the limit of active loans.',
  LOAN_MEMBER_OVERDUE: 'This member has overdue loans. They must return them before borrowing again.',
  LOAN_DUPLICATE_TITLE: 'This member already has this title on loan.',
  LOAN_ALREADY_RETURNED: 'This loan was already checked in.',
  LOAN_USER_NOT_MEMBER: 'Only members can borrow books.',
  LOAN_USER_INACTIVE: 'This account is deactivated and cannot borrow books.',

  // Users
  USER_HAS_ACTIVE_LOANS: 'This account still has active loans. Check them in before deleting it.',
  LAST_ADMIN_PROTECTED: 'The library must keep at least one active administrator.',

  // Cross-cutting
  SEARCH_QUERY_TOO_SHORT: 'Type at least two characters to search.',
  VALIDATION_FAILED: 'Some fields need fixing.',
  FORBIDDEN: 'You are not allowed to do that.',
  NOT_FOUND: 'That no longer exists. It may have been deleted.',
  RATE_LIMITED: 'Too many attempts. Wait a minute and try again.',
};

/**
 * A business refusal in plain language, ready to put in an inline alert.
 *
 * @param fallback what to say when the failure carries no envelope at all —
 *                 a dropped connection, say, rather than an answer.
 */
export function businessMessage(error: unknown, fallback = 'Something went wrong. Please try again.'): string {
  const envelope = apiError(error);

  if (!envelope) {
    return fallback;
  }

  return MESSAGES[envelope.code] ?? envelope.message ?? fallback;
}
